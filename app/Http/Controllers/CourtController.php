<?php

namespace App\Http\Controllers;

use App\Models\Court;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourtController extends Controller
{
    public function index(Request $request): View
    {
        $courts = Court::query()
            ->with('venue:id,name,address')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhereHas('venue', function ($venueQuery) use ($search) {
                            $venueQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('address', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.courts.index', compact('courts'));
    }

    public function create(): View
    {
        $venues = Venue::active()->orderBy('name')->get();

        return view('admin.courts.create', compact('venues'));
    }

    public function store(Request $request): RedirectResponse
    {
        Court::create($this->validatedData($request));

        return redirect()->route('courts.index')->with('success', 'Lapangan berhasil ditambahkan.');
    }

    public function edit(Court $court): View
    {
        $venues = Venue::active()->orderBy('name')->get();
        $court->load('venue');

        return view('admin.courts.edit', compact('court', 'venues'));
    }

    public function update(Request $request, Court $court): RedirectResponse
    {
        $court->update($this->validatedData($request));

        return redirect()->route('courts.index')->with('success', 'Data lapangan berhasil diperbarui.');
    }

    public function destroy(Court $court): RedirectResponse
    {
        if ($court->bookings()->exists()) {
            return back()->with('error', 'Lapangan masih memiliki riwayat booking dan tidak dapat dihapus.');
        }

        $court->delete();

        return redirect()->route('courts.index')->with('success', 'Lapangan berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'venue_ulid' => ['required', 'string', 'size:26', 'exists:venues,ulid'],
            'court_type' => ['required', 'in:indoor,outdoor'],
            'price_per_hour' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'description' => ['nullable', 'string', 'max:5000'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['venue_id'] = Venue::query()->where('ulid', $data['venue_ulid'])->firstOrFail()->id;
        unset($data['venue_ulid']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
