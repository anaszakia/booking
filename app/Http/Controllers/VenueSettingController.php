<?php

namespace App\Http\Controllers;

use App\Models\VenueSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VenueSettingController extends Controller
{
    public function edit(VenueSetting $venueSetting): View
    {
        return view('admin.venue-settings.edit', ['settings' => $venueSetting]);
    }

    public function update(Request $request, VenueSetting $venueSetting): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'address' => ['required', 'string', 'max:2000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'map_zoom' => ['required', 'integer', 'between:1,19'],
        ]);

        $venueSetting->update($data);

        return redirect()->route('venue-settings.edit', $venueSetting)->with('success', 'Alamat dan lokasi peta berhasil diperbarui.');
    }
}
