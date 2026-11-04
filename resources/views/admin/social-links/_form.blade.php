<form class="master-form" action="{{ $socialLink ? route('social-links.update', $socialLink) : route('social-links.store') }}" method="POST">
    @csrf
    @if ($socialLink)
        @method('PUT')
    @endif

    <div class="mb-4">
        <label for="name" class="form-label">Nama tampilan <span class="text-danger">*</span></label>
        <input id="name" name="name" class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $socialLink?->name) }}" maxlength="80" placeholder="Contoh: Instagram Rally" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="mb-4">
        <label for="platform" class="form-label">Platform & ikon <span class="text-danger">*</span></label>
        <select id="platform" name="platform" class="form-select @error('platform') is-invalid @enderror" required>
            <option value="">Pilih platform sosial media</option>
            @foreach ($platforms as $value => $platform)
                <option value="{{ $value }}" data-icon="{{ $platform['icon'] }}"
                    @selected(old('platform', $socialLink?->platform) === $value)>
                    {{ $platform['label'] }}
                </option>
            @endforeach
        </select>
        @error('platform') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="d-flex align-items-center gap-2 mt-2 text-muted small" id="platform-preview" aria-live="polite">
            <i class="ti ti-world fs-5" id="platform-preview-icon" aria-hidden="true"></i>
            <span id="platform-preview-label">Ikon platform akan tampil di footer publik</span>
        </div>
    </div>
    <div class="mb-4">
        <label for="url" class="form-label">URL profil <span class="text-danger">*</span></label>
        <input id="url" name="url" type="url" class="form-control @error('url') is-invalid @enderror"
            value="{{ old('url', $socialLink?->url) }}" maxlength="2048" placeholder="https://..." required>
        @error('url') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="mb-4">
        <label for="sort_order" class="form-label">Urutan</label>
        <input id="sort_order" name="sort_order" type="number" min="0"
            class="form-control @error('sort_order') is-invalid @enderror"
            value="{{ old('sort_order', $socialLink?->sort_order ?? 0) }}">
        @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="form-check form-switch mb-4">
        <input id="is_active" name="is_active" type="checkbox" value="1" class="form-check-input"
            @checked(old('is_active', $socialLink?->is_active ?? true))>
        <label for="is_active" class="form-check-label">Tampilkan di footer publik</label>
    </div>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('social-links.index') }}" class="btn btn-white">Batal</a>
</form>

@push('scripts')
    <script>
        const platformSelect = document.getElementById('platform');
        const platformPreviewIcon = document.getElementById('platform-preview-icon');
        const platformPreviewLabel = document.getElementById('platform-preview-label');

        function updatePlatformPreview() {
            const selectedOption = platformSelect.selectedOptions[0];
            platformPreviewIcon.className = `${selectedOption?.dataset.icon || 'ti ti-world'} fs-5`;
            platformPreviewLabel.textContent = selectedOption?.value
                ? `Ikon ${selectedOption.text.trim()}`
                : 'Ikon platform akan tampil di footer publik';
        }

        platformSelect.addEventListener('change', updatePlatformPreview);
        updatePlatformPreview();
    </script>
@endpush
