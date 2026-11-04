@extends('layouts.app')

@section('title', 'Buat Booking')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h4 class="mb-1">Buat Booking untuk Pelanggan</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bookings.index') }}">Daftar Booking</a></li>
                    <li class="breadcrumb-item active">Buat Booking</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('bookings.index') }}" class="btn btn-white"><i class="ti ti-arrow-left me-1"></i> Kembali</a>
    </div>

    <div class="card card-lg">
        <div class="card-body">
            @if ($courts->isEmpty())
                <div class="alert alert-warning mb-0">Belum ada lapangan aktif pada venue aktif. Aktifkan lapangan dan venue terlebih dahulu.</div>
            @else
                <form action="{{ route('bookings.store') }}" method="POST" class="admin-form">
                    @csrf
                    <div class="row g-4">
                        <div class="col-12">
                            <h5 class="mb-0">Jadwal bermain</h5>
                            <p class="text-muted small mb-0">Slot yang sudah dipesan atau telah lewat tidak dapat dipilih.</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="court_ulid">Venue / lapangan</label>
                            <select class="form-select @error('court_ulid') is-invalid @enderror" id="court_ulid" name="court_ulid" required>
                                <option value="">Pilih lapangan</option>
                                @foreach ($courts as $court)
                                    <option value="{{ $court->ulid }}" @selected(old('court_ulid') === $court->ulid)>
                                        {{ $court->venue->name }} — {{ $court->name }} (Rp{{ number_format($court->price_per_hour, 0, ',', '.') }}/jam)
                                    </option>
                                @endforeach
                            </select>
                            @error('court_ulid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="booking_date">Tanggal</label>
                            <input class="form-control @error('booking_date') is-invalid @enderror" id="booking_date" name="booking_date" type="date" min="{{ now()->toDateString() }}" max="{{ now()->addDays(365)->toDateString() }}" value="{{ old('booking_date', now()->toDateString()) }}" required>
                            @error('booking_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="booking_time">Jam mulai</label>
                            <select class="form-select @error('booking_time') is-invalid @enderror" id="booking_time" name="booking_time" required disabled>
                                <option value="">Pilih lapangan dan tanggal</option>
                            </select>
                            @error('booking_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text" id="availability-message" aria-live="polite"></div>
                        </div>

                        <div class="col-12 pt-2">
                            <h5 class="mb-0">Data pelanggan</h5>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="name">Nama lengkap</label>
                            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" maxlength="120" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="phone">Nomor telepon</label>
                            <input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" type="tel" value="{{ old('phone') }}" maxlength="32" required>
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email <span class="text-muted">(opsional)</span></label>
                            <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" maxlength="254">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="notes">Catatan <span class="text-muted">(opsional)</span></label>
                            <input class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" value="{{ old('notes') }}" maxlength="2000">
                            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 pt-2">
                            <h5 class="mb-0">Pembayaran</h5>
                            <p class="text-muted small mb-0">Pilih pembayaran online untuk dikirim ke halaman pembayaran, atau catat pembayaran yang diterima di lokasi.</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="payment_option">Cara pembayaran</label>
                            <select class="form-select @error('payment_option') is-invalid @enderror" id="payment_option" name="payment_option" required>
                                <option value="online" @selected(old('payment_option', 'online') === 'online')>Pembayaran online — menunggu pembayaran 30 menit</option>
                                <option value="onsite" @selected(old('payment_option') === 'onsite')>Dibayar di lokasi — konfirmasi langsung</option>
                            </select>
                            @error('payment_option') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6" id="payment-method-field" hidden>
                            <label class="form-label" for="payment_method">Metode pembayaran di lokasi</label>
                            <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" disabled>
                                <option value="">Pilih metode pembayaran</option>
                                <option value="cash" @selected(old('payment_method') === 'cash')>Tunai</option>
                                <option value="qris" @selected(old('payment_method') === 'qris')>QRIS</option>
                                <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Transfer bank</option>
                            </select>
                            @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                            <button type="submit" class="btn btn-primary"><i class="ti ti-calendar-check me-1"></i> Buat Booking</button>
                            <a href="{{ route('bookings.index') }}" class="btn btn-white">Batal</a>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const court = document.getElementById('court_ulid');
            const date = document.getElementById('booking_date');
            const time = document.getElementById('booking_time');
            const message = document.getElementById('availability-message');
            const paymentOption = document.getElementById('payment_option');
            const paymentMethodField = document.getElementById('payment-method-field');
            const paymentMethod = document.getElementById('payment_method');
            const slots = ['07:00', '09:00', '11:00', '13:00', '15:00', '17:00', '19:00', '21:00'];
            const availabilityUrl = @json(route('booking.availability'));
            const selectedTime = @json(old('booking_time'));

            const syncPaymentMethod = () => {
                const onsite = paymentOption.value === 'onsite';
                paymentMethodField.hidden = !onsite;
                paymentMethod.disabled = !onsite;
                paymentMethod.required = onsite;
            };

            const loadAvailability = async () => {
                time.replaceChildren(new Option('Memuat jadwal...', ''));
                time.disabled = true;
                message.textContent = '';
                if (!court.value || !date.value) {
                    time.replaceChildren(new Option('Pilih lapangan dan tanggal', ''));
                    return;
                }

                try {
                    const query = new URLSearchParams({ from: date.value, to: date.value });
                    const response = await fetch(`${availabilityUrl}?${query}`);
                    if (!response.ok) {
                        throw new Error('Jadwal tidak dapat dimuat. Muat ulang halaman atau coba lagi.');
                    }
                    const { bookings } = await response.json();
                    const bookedTimes = new Set(bookings
                        .filter((booking) => booking.court === court.value && booking.date === date.value)
                        .map((booking) => booking.time));
                    const now = new Date();
                    const today = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
                    const nowTime = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;

                    time.replaceChildren(new Option('Pilih jam tersedia', ''));
                    let availableCount = 0;
                    slots.forEach((slot) => {
                        const option = new Option(slot, slot);
                        const hasPassed = date.value === today && slot <= nowTime;
                        option.disabled = bookedTimes.has(slot) || hasPassed;
                        if (!option.disabled) availableCount += 1;
                        if (slot === selectedTime && !option.disabled) option.selected = true;
                        time.add(option);
                    });
                    time.disabled = availableCount === 0;
                    message.textContent = availableCount ? `${availableCount} slot tersedia.` : 'Tidak ada slot tersedia pada tanggal ini.';
                } catch (error) {
                    time.replaceChildren(new Option('Jadwal gagal dimuat', ''));
                    message.textContent = error.message;
                }
            };

            court.addEventListener('change', loadAvailability);
            date.addEventListener('change', loadAvailability);
            paymentOption.addEventListener('change', syncPaymentMethod);
            syncPaymentMethod();
            loadAvailability();
        })();
    </script>
@endpush
