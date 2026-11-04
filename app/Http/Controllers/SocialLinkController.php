<?php

namespace App\Http\Controllers;

use App\Models\SocialLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SocialLinkController extends Controller
{
    public function index(): View
    {
        $socialLinks = SocialLink::query()->orderBy('sort_order')->orderBy('name')->paginate(10);

        return view('admin.social-links.index', compact('socialLinks'));
    }

    public function create(): View
    {
        return view('admin.social-links.create', ['platforms' => SocialLink::PLATFORMS]);
    }

    public function store(Request $request): RedirectResponse
    {
        SocialLink::create($this->validatedData($request));

        return redirect()->route('social-links.index')->with('success', 'Tautan sosial berhasil ditambahkan.');
    }

    public function edit(SocialLink $socialLink): View
    {
        return view('admin.social-links.edit', [
            'socialLink' => $socialLink,
            'platforms' => SocialLink::PLATFORMS,
        ]);
    }

    public function update(Request $request, SocialLink $socialLink): RedirectResponse
    {
        $socialLink->update($this->validatedData($request));

        return redirect()->route('social-links.index')->with('success', 'Tautan sosial berhasil diperbarui.');
    }

    public function destroy(SocialLink $socialLink): RedirectResponse
    {
        $socialLink->delete();

        return redirect()->route('social-links.index')->with('success', 'Tautan sosial berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'platform' => ['required', 'string', Rule::in(array_keys(SocialLink::PLATFORMS))],
            'url' => ['required', 'url:http,https', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
