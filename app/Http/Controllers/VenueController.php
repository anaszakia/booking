<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use App\Services\MinioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

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
        unset($data['bank_accounts'], $data['logo'], $data['qris'], $data['remove_qris']);

        $data['logo_path'] = $request->hasFile('logo')
            ? $this->minio->upload($request->file('logo'), $this->minio->buildPath('venues'))
            : null;
        $data['qris_path'] = $request->hasFile('qris')
            ? $this->minio->upload($request->file('qris'), $this->minio->buildPath('venues/qris'))
            : null;

        DB::transaction(function () use ($data, $bankAccounts): void {
            $venue = Venue::create($data);
            $venue->bankAccounts()->createMany($bankAccounts);
        });

        return redirect()->route('venues.index')->with('success', 'Venue berhasil ditambahkan.');
    }

    public function edit(Venue $venue): View
    {
        $venue->load('bankAccounts');

        return view('admin.venues.edit', compact('venue'));
    }

    public function update(Request $request, Venue $venue): RedirectResponse
    {
        $data = $this->validatedData($request);
        $bankAccounts = $data['bank_accounts'] ?? [];
        unset($data['bank_accounts'], $data['logo'], $data['qris'], $data['remove_qris']);

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

        DB::transaction(function () use ($venue, $data, $bankAccounts): void {
            $venue->update($data);
            $venue->bankAccounts()->delete();
            $venue->bankAccounts()->createMany($bankAccounts);
        });
        $this->deleteOldAssets($oldAssets, $venue);

        return redirect()->route('venues.index')->with('success', 'Data venue berhasil diperbarui.');
    }

    public function destroy(Venue $venue): RedirectResponse
    {
        if ($venue->courts()->exists() || $venue->bookings()->exists()) {
            return back()->with('error', 'Venue masih dipakai lapangan atau riwayat booking. Pindahkan lapangan terlebih dahulu.');
        }

        $assets = [$venue->logo_path, $venue->qris_path];
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
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'qris' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'remove_qris' => ['sometimes', 'boolean'],
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
}
