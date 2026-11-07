@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

<style>
    .master-form .form-control,
    .master-form .form-control::placeholder { color: #000 !important; }
    #venue-picker-map { height: 380px; border: 1px solid #dfe5df; border-radius: 12px; z-index: 0; }
    .bank-account-row { background: #f8faf8; }
    .hero-image-preview { width: 100%; height: 130px; object-fit: cover; }
</style>

<form class="master-form" action="{{ $venue->exists ? route('venues.update', $venue) : route('venues.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if ($venue->exists)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-lg-7">
            <div class="mb-4">
                <label for="name" class="form-label">Nama venue <span class="text-danger">*</span></label>
                <input id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $venue->name) }}" maxlength="120" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-4">
                <label for="address" class="form-label">Alamat <span class="text-danger">*</span></label>
                <textarea id="address" name="address" rows="3"
                    class="form-control @error('address') is-invalid @enderror" required>{{ old('address', $venue->address) }}</textarea>
                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-4">
                <label class="form-label">Warna tema frontend</label>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label for="primary_color" class="form-label small">Warna utama</label>
                        <input id="primary_color" name="primary_color" type="color"
                            value="{{ old('primary_color', $venue->primary_color ?? '#245c43') }}"
                            class="form-control form-control-color @error('primary_color') is-invalid @enderror" title="Pilih warna utama">
                        @error('primary_color') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-sm-6">
                        <label for="accent_color" class="form-label small">Warna aksen</label>
                        <input id="accent_color" name="accent_color" type="color"
                            value="{{ old('accent_color', $venue->accent_color ?? '#d7f36a') }}"
                            class="form-control form-control-color @error('accent_color') is-invalid @enderror" title="Pilih warna aksen">
                        @error('accent_color') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="form-text">Warna utama dipakai untuk elemen brand dan tombol. Warna aksen dipakai untuk highlight dan tombol utama.</div>
            </div>
            <div class="mb-4">
                <label for="logo" class="form-label">Logo venue</label>
                <input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp"
                    class="form-control @error('logo') is-invalid @enderror">
                <div class="form-text">Format JPG, PNG, atau WebP, maksimal 4 MB.</div>
                @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @if ($venue->logo_path)
                    <div class="mt-3">
                        <img src="{{ minio_url($venue->logo_path) }}" alt="Logo {{ $venue->name }}" width="88" height="88" class="rounded object-fit-contain border p-2">
                    </div>
                @endif
            </div>
            <div class="mb-4">
                <label for="hero_images" class="form-label">Foto hero</label>
                <input id="hero_images" name="hero_images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple
                    class="form-control @error('hero_images') is-invalid @enderror @error('hero_images.*') is-invalid @enderror">
                <div class="form-text">Pilih beberapa foto JPG, PNG, atau WebP, maksimal 8 MB per foto dan 10 foto per venue. Jika lebih dari satu foto, gambar akan berganti otomatis di hero halaman depan.</div>
                @error('hero_images') <div class="text-danger small">{{ $message }}</div> @enderror
                @error('hero_images.*') <div class="text-danger small">{{ $message }}</div> @enderror
                @error('delete_hero_images') <div class="text-danger small">{{ $message }}</div> @enderror
                @if ($venue->exists && $venue->heroImages->isNotEmpty())
                    <div class="row g-3 mt-1">
                        @foreach ($venue->heroImages as $heroImage)
                            <div class="col-sm-6 col-xl-4">
                                <div class="border rounded p-2 h-100">
                                    <img src="{{ minio_url($heroImage->image_path) }}" alt="Foto hero {{ $venue->name }} {{ $loop->iteration }}" class="hero-image-preview rounded">
                                    <label class="form-check mt-2 mb-0">
                                        <input type="checkbox" name="delete_hero_images[]" value="{{ $heroImage->id }}" class="form-check-input" @checked(in_array($heroImage->id, old('delete_hero_images', [])))>
                                        <span class="form-check-label">Hapus foto ini</span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="mb-4">
                <label for="qris" class="form-label">QRIS</label>
                <input id="qris" name="qris" type="file" accept="image/jpeg,image/png,image/webp"
                    class="form-control @error('qris') is-invalid @enderror">
                <div class="form-text">Format JPG, PNG, atau WebP, maksimal 8 MB.</div>
                @error('qris') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @if ($venue->qris_path)
                    <div class="mt-3 d-flex align-items-center gap-3">
                        <img src="{{ minio_url($venue->qris_path) }}" alt="QRIS {{ $venue->name }}" width="120" height="120" class="rounded object-fit-contain border p-2">
                        <label class="form-check">
                            <input type="checkbox" name="remove_qris" value="1" class="form-check-input" @checked(old('remove_qris'))>
                            <span class="form-check-label">Hapus QRIS saat disimpan</span>
                        </label>
                    </div>
                @endif
            </div>
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                    <div>
                        <label class="form-label mb-0">Rekening bank</label>
                        <div class="form-text">Tambahkan satu atau beberapa rekening untuk venue ini.</div>
                    </div>
                    <button class="btn btn-sm btn-white" id="add-bank-account" type="button">
                        <i class="ti ti-plus me-1"></i>Tambah rekening
                    </button>
                </div>
                @error('bank_accounts') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                @php
                    $bankAccounts = old('bank_accounts', $venue->bankAccounts->map(fn ($account) => [
                        'bank_name' => $account->bank_name,
                        'account_name' => $account->account_name,
                        'account_number' => $account->account_number,
                    ])->all());
                    $bankAccounts = is_array($bankAccounts) ? array_slice($bankAccounts, 0, 20, true) : [];
                @endphp
                <div id="bank-account-list">
                    @foreach ($bankAccounts as $index => $bankAccount)
                        <div class="bank-account-row border rounded p-3 mb-2" data-index="{{ $index }}">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <strong>Rekening <span class="bank-account-number">{{ $loop->iteration }}</span></strong>
                                <button class="btn btn-sm btn-white text-danger remove-bank-account" type="button">Hapus</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label" for="bank-name-{{ $index }}">Nama bank</label>
                                    <input id="bank-name-{{ $index }}" name="bank_accounts[{{ $index }}][bank_name]"
                                        value="{{ $bankAccount['bank_name'] ?? '' }}" maxlength="100" required
                                        class="form-control @error("bank_accounts.$index.bank_name") is-invalid @enderror">
                                    @error("bank_accounts.$index.bank_name") <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="account-name-{{ $index }}">Nama pemilik rekening</label>
                                    <input id="account-name-{{ $index }}" name="bank_accounts[{{ $index }}][account_name]"
                                        value="{{ $bankAccount['account_name'] ?? '' }}" maxlength="120" required
                                        class="form-control @error("bank_accounts.$index.account_name") is-invalid @enderror">
                                    @error("bank_accounts.$index.account_name") <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="account-number-{{ $index }}">Nomor rekening</label>
                                    <input id="account-number-{{ $index }}" name="bank_accounts[{{ $index }}][account_number]"
                                        value="{{ $bankAccount['account_number'] ?? '' }}" maxlength="64" required
                                        class="form-control @error("bank_accounts.$index.account_number") is-invalid @enderror">
                                    @error("bank_accounts.$index.account_number") <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <template id="bank-account-template">
                    <div class="bank-account-row border rounded p-3 mb-2" data-index="__INDEX__">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <strong>Rekening <span class="bank-account-number"></span></strong>
                            <button class="btn btn-sm btn-white text-danger remove-bank-account" type="button">Hapus</button>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label" for="bank-name-__INDEX__">Nama bank</label>
                                <input id="bank-name-__INDEX__" name="bank_accounts[__INDEX__][bank_name]" maxlength="100" required class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="account-name-__INDEX__">Nama pemilik rekening</label>
                                <input id="account-name-__INDEX__" name="bank_accounts[__INDEX__][account_name]" maxlength="120" required class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="account-number-__INDEX__">Nomor rekening</label>
                                <input id="account-number-__INDEX__" name="bank_accounts[__INDEX__][account_number]" maxlength="64" required class="form-control">
                            </div>
                        </div>
                    </div>
                </template>
                <div class="form-text">Maksimal 20 rekening per venue.</div>
            </div>
            <div class="form-check form-switch mb-4">
                <input id="is_active" name="is_active" type="checkbox" value="1" class="form-check-input"
                    @checked(old('is_active', $venue->is_active ?? true))>
                <label for="is_active" class="form-check-label">Venue aktif dan tampil di halaman publik</label>
            </div>
        </div>

        <div class="col-lg-5">
            <label class="form-label" for="venue-picker-map">Pilih lokasi di peta <span class="text-danger">*</span></label>
            <p class="form-text">Klik peta atau geser marker untuk menentukan titik venue.</p>
            <div id="venue-picker-map" role="application" aria-label="Peta untuk memilih lokasi venue"></div>
            <p id="venue-picker-status" class="form-text mt-2" aria-live="polite">Klik peta untuk memilih lokasi.</p>
            @error('latitude') <div class="text-danger small">{{ $message }}</div> @enderror
            @error('longitude') <div class="text-danger small">{{ $message }}</div> @enderror
            <input id="latitude" name="latitude" type="hidden" value="{{ old('latitude', $venue->latitude) }}" required>
            <input id="longitude" name="longitude" type="hidden" value="{{ old('longitude', $venue->longitude) }}" required>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
        <a href="{{ route('venues.index') }}" class="btn btn-white">Batal</a>
    </div>
</form>

<script>
    (() => {
        const accountList = document.getElementById('bank-account-list');
        const accountTemplate = document.getElementById('bank-account-template');
        const addButton = document.getElementById('add-bank-account');
        let nextIndex = [...accountList.children].reduce((highest, row) => Math.max(highest, Number(row.dataset.index)), -1) + 1;

        const updateBankAccounts = () => {
            [...accountList.children].forEach((row, index) => {
                row.querySelector('.bank-account-number').textContent = String(index + 1);
            });
            addButton.disabled = accountList.children.length >= 20;
        };

        addButton.addEventListener('click', () => {
            if (accountList.children.length >= 20) return;
            accountList.insertAdjacentHTML('beforeend', accountTemplate.innerHTML.replaceAll('__INDEX__', String(nextIndex++)));
            updateBankAccounts();
        });
        accountList.addEventListener('click', (event) => {
            const removeButton = event.target.closest('.remove-bank-account');
            if (!removeButton) return;
            removeButton.closest('.bank-account-row').remove();
            updateBankAccounts();
        });
        updateBankAccounts();
    })();
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    (() => {
        const mapElement = document.getElementById('venue-picker-map');
        const latitudeInput = document.getElementById('latitude');
        const longitudeInput = document.getElementById('longitude');
        const status = document.getElementById('venue-picker-status');
        const initialLatitude = Number(latitudeInput.value);
        const initialLongitude = Number(longitudeInput.value);

        if (!window.L) {
            status.textContent = 'Peta gagal dimuat. Periksa koneksi internet lalu muat ulang halaman.';
            status.classList.add('text-danger');
            return;
        }

        const hasCoordinates = Number.isFinite(initialLatitude) && Number.isFinite(initialLongitude);
        const initialPoint = hasCoordinates ? [initialLatitude, initialLongitude] : [-6.2, 106.8167];
        const map = L.map(mapElement).setView(initialPoint, hasCoordinates ? 16 : 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap',
        }).addTo(map);

        const marker = L.marker(initialPoint, { draggable: true }).addTo(map);
        const updateCoordinates = (point) => {
            latitudeInput.value = point.lat.toFixed(7);
            longitudeInput.value = point.lng.toFixed(7);
            status.textContent = `Lokasi dipilih: ${latitudeInput.value}, ${longitudeInput.value}`;
        };

        map.on('click', (event) => {
            marker.setLatLng(event.latlng);
            updateCoordinates(event.latlng);
        });
        marker.on('dragend', () => updateCoordinates(marker.getLatLng()));
        if (hasCoordinates) updateCoordinates(marker.getLatLng());
        window.setTimeout(() => map.invalidateSize(), 150);
    })();
</script>
