<style>
    .court-form .form-control,
    .court-form .form-select,
    .court-form .form-control::placeholder {
        color: #000 !important;
    }
</style>

<form class="court-form" action="{{ $court ? route('courts.update', $court) : route('courts.store') }}" method="POST">
    @csrf
    @if ($court)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="mb-4">
                <label for="name" class="form-label">Nama lapangan <span class="text-danger">*</span></label>
                <input id="name" name="name" type="text" maxlength="120"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $court?->name) }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-4">
                <label for="location" class="form-label">Area / lokasi <span class="text-danger">*</span></label>
                <input id="location" name="location" type="text" maxlength="120"
                    class="form-control @error('location') is-invalid @enderror"
                    value="{{ old('location', $court?->location) }}" placeholder="Contoh: Kemang, Jakarta Selatan" required>
                @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-4">
                <label for="address" class="form-label">Alamat lengkap</label>
                <textarea id="address" name="address" rows="2"
                    class="form-control @error('address') is-invalid @enderror">{{ old('address', $court?->address) }}</textarea>
                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label for="court_type" class="form-label">Tipe lapangan <span class="text-danger">*</span></label>
                    <select id="court_type" name="court_type"
                        class="form-select @error('court_type') is-invalid @enderror" required>
                        <option value="indoor" @selected(old('court_type', $court?->court_type ?? 'indoor') === 'indoor')>Indoor</option>
                        <option value="outdoor" @selected(old('court_type', $court?->court_type) === 'outdoor')>Outdoor</option>
                    </select>
                    @error('court_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-4">
                    <label for="price_per_hour" class="form-label">Harga per jam (Rp) <span class="text-danger">*</span></label>
                    <input id="price_per_hour" name="price_per_hour" type="number" min="0" step="1"
                        class="form-control @error('price_per_hour') is-invalid @enderror"
                        value="{{ old('price_per_hour', $court?->price_per_hour) }}" required>
                    @error('price_per_hour') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="image_url" class="form-label">URL foto</label>
                <input id="image_url" name="image_url" type="url" maxlength="2048"
                    class="form-control @error('image_url') is-invalid @enderror"
                    value="{{ old('image_url', $court?->image_url) }}" placeholder="https://...">
                @error('image_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea id="description" name="description" rows="4"
                    class="form-control @error('description') is-invalid @enderror">{{ old('description', $court?->description) }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-check form-switch mb-4">
                <input id="is_active" name="is_active" type="checkbox" value="1" class="form-check-input"
                    @checked(old('is_active', $court?->is_active ?? true))>
                <label for="is_active" class="form-check-label">Lapangan aktif dan dapat ditampilkan</label>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
        <a href="{{ route('courts.index') }}" class="btn btn-white">Batal</a>
    </div>
</form>
