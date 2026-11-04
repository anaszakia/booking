<?php

namespace App\Http\Controllers;

use App\Models\Court;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourtController extends Controller
{
    public function index(Request $request): View
    {
        $courts = Court::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.courts.index', compact('courts'));
    }

    public function create(): View
    {
        return view('admin.courts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Court::create($this->validatedData($request));

        return redirect()->route('courts.index')->with('success', 'Lapangan berhasil ditambahkan.');
    }

    public function edit(Court $court): View
    {
        return view('admin.courts.edit', compact('court'));
    }

    public function update(Request $request, Court $court): RedirectResponse
    {
        $court->update($this->validatedData($request));

        return redirect()->route('courts.index')->with('success', 'Data lapangan berhasil diperbarui.');
    }

    public function destroy(Court $court): RedirectResponse
    {
        $court->delete();

        return redirect()->route('courts.index')->with('success', 'Lapangan berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'location' => ['required', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:5000'],
            'court_type' => ['required', 'in:indoor,outdoor'],
            'price_per_hour' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'description' => ['nullable', 'string', 'max:5000'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
