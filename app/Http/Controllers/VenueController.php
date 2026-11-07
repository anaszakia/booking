<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use App\Services\MinioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class VenueController extends Controller
{
    public function __construct(private readonly MinioService $minio) {}

    public function index(): View
    {
        $venues = Venue::query()->withCount('bankAccounts')->orderBy('name')->paginate(10);

        return view('admin.venues.index', compact('venues'));
    }

    public function create(): View
    {
        return view('admin.venues.create', ['venue' => new Venue([
            'latitude' => -6.2607,
            'longitude' => 106.8167,
            'is_active' => true,
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $bankAccounts = $data['bank_accounts'] ?? [];
        $heroImages = $data['hero_images'] ?? [];
        unset($data['bank_accounts'], $data['hero_images'], $data['delete_hero_images'], $data['logo'], $data['qris'], $data['remove_qris']);

        $data['logo_path'] = $request->hasFile('logo')
            ? $this->minio->upload($request->file('logo'), $this->minio->buildPath('venues'))
            : null;
        $data['qris_path'] = $request->hasFile('qris')
            ? $this->minio->upload($request->file('qris'), $this->minio->buildPath('venues/qris'))
            : null;

        $uploadedHeroImages = [];
        try {
            foreach ($heroImages as $image) {
                $uploadedHeroImages[] = $this->minio->upload($image, $this->minio->buildPath('venues/hero'));
            }

            DB::transaction(function () use ($data, $bankAccounts, $uploadedHeroImages): void {
                $venue = Venue::create($data);
                $venue->bankAccounts()->createMany($bankAccounts);
                $venue->heroImages()->createMany(array_map(
                    fn (string $path, int $index) => ['image_path' => $path, 'sort_order' => $index],
                    $uploadedHeroImages,
                    array_keys($uploadedHeroImages),
                ));
            });
        } catch (Throwable $exception) {
            $this->deleteUploadedHeroImages($uploadedHeroImages);
            throw $exception;
        }

        return redirect()->route('venues.index')->with('success', 'Venue berhasil ditambahkan.');
    }

    public function edit(Venue $venue): View
    {
        $venue->load(['bankAccounts', 'heroImages']);

        return view('admin.venues.edit', compact('venue'));
    }

    public function update(Request $request, Venue $venue): RedirectResponse
    {
        $data = $this->validatedData($request);
        $bankAccounts = $data['bank_accounts'] ?? [];
        $heroImages = $data['hero_images'] ?? [];
        $deleteHeroImageIds = array_map('intval', $data['delete_hero_images'] ?? []);
        unset($data['bank_accounts'], $data['hero_images'], $data['delete_hero_images'], $data['logo'], $data['qris'], $data['remove_qris']);

        $oldAssets = [];
        if ($request->hasFile('logo')) {
            $oldAssets[] = $venue->logo_path;
            $data['logo_path'] = $this->minio->upload($request->file('logo'), $this->minio->buildPath('venues'));
        }
        if ($request->hasFile('qris')) {
            $oldAssets[] = $venue->qris_path;
            $data['qris_path'] = $this->minio->upload($request->file('qris'), $this->minio->buildPath('venues/qris'));
        } elseif ($request->boolean('remove_qris')) {
            $oldAssets[] = $venue->qris_path;
            $data['qris_path'] = null;
        }

        $uploadedHeroImages = [];
        $heroImagesToDelete = [];
        try {
            foreach ($heroImages as $image) {
                $uploadedHeroImages[] = $this->minio->upload($image, $this->minio->buildPath('venues/hero'));
            }

            DB::transaction(function () use ($venue, $data, $bankAccounts, $uploadedHeroImages, $deleteHeroImageIds, &$heroImagesToDelete): void {
                $lockedVenue = Venue::query()->whereKey($venue->id)->lockForUpdate()->firstOrFail();
                $heroImagesToDelete = $lockedVenue->heroImages()
                    ->whereIn('id', $deleteHeroImageIds)
                    ->get();
                if ($heroImagesToDelete->count() !== count(array_unique($deleteHeroImageIds))) {
                    throw ValidationException::withMessages([
                        'delete_hero_images' => 'Foto hero yang dipilih tidak valid untuk venue ini. Muat ulang halaman lalu coba lagi.',
                    ]);
                }
                if ($lockedVenue->heroImages()->count() - $heroImagesToDelete->count() + count($uploadedHeroImages) > 10) {
                    throw ValidationException::withMessages([
                        'hero_images' => 'Setiap venue dapat memiliki maksimal 10 foto hero. Hapus foto lama atau kurangi foto baru.',
                    ]);
                }

                $lockedVenue->update($data);
                $lockedVenue->bankAccounts()->delete();
                $lockedVenue->bankAccounts()->createMany($bankAccounts);
                $lockedVenue->heroImages()->whereIn('id', $heroImagesToDelete->modelKeys())->delete();
                $nextOrder = (int) $lockedVenue->heroImages()->max('sort_order') + 1;
                $lockedVenue->heroImages()->createMany(array_map(
                    fn (string $path, int $index) => ['image_path' => $path, 'sort_order' => $nextOrder + $index],
                    $uploadedHeroImages,
                    array_keys($uploadedHeroImages),
                ));
            });
        } catch (Throwable $exception) {
            $this->deleteUploadedHeroImages($uploadedHeroImages);
            throw $exception;
        }
        $oldAssets = array_merge($oldAssets, $heroImagesToDelete->pluck('image_path')->all());
        $this->deleteOldAssets($oldAssets, $venue);

        return redirect()->route('venues.index')->with('success', 'Data venue berhasil diperbarui.');
    }

    public function destroy(Venue $venue): RedirectResponse
    {
        if ($venue->courts()->exists() || $venue->bookings()->exists()) {
            return back()->with('error', 'Venue masih dipakai lapangan atau riwayat booking. Pindahkan lapangan terlebih dahulu.');
        }

        $assets = array_merge(
            [$venue->logo_path, $venue->qris_path],
            $venue->heroImages()->pluck('image_path')->all(),
        );
        $venue->delete();
        $this->deleteOldAssets($assets, $venue);

        return redirect()->route('venues.index')->with('success', 'Venue berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'address' => ['required', 'string', 'max:2000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'primary_color' => ['sometimes', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['sometimes', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'hero_eyebrow' => ['sometimes', 'nullable', 'string', 'max:120'],
            'hero_title' => ['sometimes', 'nullable', 'string', 'max:120'],
            'hero_title_highlight' => ['sometimes', 'nullable', 'string', 'max:120'],
            'hero_subtitle' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'qris' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'remove_qris' => ['sometimes', 'boolean'],
            'hero_images' => ['sometimes', 'array', 'max:10'],
            'hero_images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'delete_hero_images' => ['sometimes', 'array', 'max:10'],
            'delete_hero_images.*' => ['integer', 'distinct', 'exists:venue_hero_images,id'],
            'bank_accounts' => ['sometimes', 'array', 'max:20'],
            'bank_accounts.*.bank_name' => ['required', 'string', 'max:100'],
            'bank_accounts.*.account_name' => ['required', 'string', 'max:120'],
            'bank_accounts.*.account_number' => ['required', 'string', 'max:64'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function deleteOldAssets(array $paths, Venue $venue): void
    {
        foreach (array_filter($paths) as $path) {
            if (! $this->minio->delete($path)) {
                Log::warning('Gagal menghapus file venue lama.', ['venue_ulid' => $venue->ulid, 'file_path' => $path]);
            }
        }
    }

    private function deleteUploadedHeroImages(array $paths): void
    {
        foreach ($paths as $path) {
            if (! $this->minio->delete($path)) {
                Log::warning('Gagal menghapus foto hero venue yang tidak tersimpan.', ['file_path' => $path]);
            }
        }
    }
}
