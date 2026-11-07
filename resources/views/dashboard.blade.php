@extends('layouts.app')

@section('title', 'Dashboard Booking')

@section('content')
    @php
        $statusLabels = [
            'pending_payment' => 'Menunggu pembayaran',
            'pending_verification' => 'Menunggu verifikasi',
            'confirmed' => 'Dikonfirmasi',
        ];
        $statusClasses = [
            'pending_payment' => 'bg-warning-subtle text-warning-emphasis',
            'pending_verification' => 'bg-info-subtle text-info-emphasis',
            'confirmed' => 'bg-success-subtle text-success-emphasis',
        ];
        $bookingHoursToday = $todayBookings->sum('duration_hours');
        $bookingValueToday = $todayBookings->sum('amount');
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-6">
        <div>
            <h3 class="mb-1">Dashboard Booking</h3>
            <p class="text-secondary mb-0">
                Ringkasan operasional · {{ now()->locale('id')->translatedFormat('l, d F Y') }}
            </p>
        </div>
        @if ($canViewBookings)
            <div class="d-flex gap-2">
                <a href="{{ route('bookings.create') }}" class="btn btn-primary">
                    <i class="ti ti-calendar-plus me-1"></i> Buat booking
                </a>
                <a href="{{ route('bookings.calendar') }}" class="btn btn-white">
                    <i class="ti ti-calendar me-1"></i> Kalender
                </a>
            </div>
        @endif
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4 mb-6">
        <div class="col">
            <div class="card card-lg h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-secondary mb-2">Booking aktif hari ini</p>
                            <div class="fs-2 fw-bold">{{ $todayBookings->count() }}</div>
                        </div>
                        <span class="icon-shape icon-lg rounded-circle bg-primary-subtle text-primary-emphasis">
                            <i class="ti ti-calendar-event fs-4"></i>
                        </span>
                    </div>
                    <div class="small text-secondary mt-3">Termasuk booking yang menunggu pembayaran atau verifikasi.</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card card-lg h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-secondary mb-2">Jam lapangan terpesan</p>
                            <div class="fs-2 fw-bold">{{ $bookingHoursToday }} <span class="fs-5 fw-medium">jam</span></div>
                        </div>
                        <span class="icon-shape icon-lg rounded-circle bg-success-subtle text-success-emphasis">
                            <i class="ti ti-clock-hour-4 fs-4"></i>
                        </span>
                    </div>
                    <div class="small text-secondary mt-3">Akumulasi durasi booking aktif hari ini.</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card card-lg h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-secondary mb-2">Nilai booking hari ini</p>
                            <div class="fs-3 fw-bold">Rp{{ number_format($bookingValueToday, 0, ',', '.') }}</div>
                        </div>
                        <span class="icon-shape icon-lg rounded-circle bg-info-subtle text-info-emphasis">
                            <i class="ti ti-cash fs-4"></i>
                        </span>
                    </div>
                    <div class="small text-secondary mt-3">Nilai booking aktif, bukan laporan pembayaran terverifikasi.</div>
                </div>
            </div>
        </div>
        @if ($isAdmin)
            <div class="col">
                <div class="card card-lg h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-secondary mb-2">Bukti bayar perlu dicek</p>
                                <div class="fs-2 fw-bold">{{ $pendingProofCount }}</div>
                            </div>
                            <span class="icon-shape icon-lg rounded-circle bg-warning-subtle text-warning-emphasis">
                                <i class="ti ti-receipt fs-4"></i>
                            </span>
                        </div>
                        <div class="small text-secondary mt-3">Bukti yang sudah diunggah dan menunggu verifikasi.</div>
                    </div>
                </div>
            </div>
        @else
            <div class="col">
                <div class="card card-lg h-100">
                    <div class="card-body">
                        <p class="text-secondary mb-2">Status booking hari ini</p>
                        @foreach ($statusLabels as $status => $label)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge {{ $statusClasses[$status] }}">{{ $label }}</span>
                                <strong>{{ $todayBookings->where('status', $status)->count() }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if ($isAdmin)
        <div class="card card-lg mb-6">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="mb-1">Bukti pembayaran menunggu pemeriksaan</h5>
                    <p class="text-secondary small mb-0">Urut dari bukti yang paling lama menunggu.</p>
                </div>
                <a href="{{ route('bookings.index') }}" class="btn btn-white btn-sm">Semua booking</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-centered text-nowrap mb-0">
                    <thead>
                        <tr><th>Nomor booking</th><th>Pelanggan</th><th>Lapangan</th><th>Jadwal</th><th>Bukti diunggah</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingProofs as $booking)
                            <tr>
                                <td><code>{{ $booking->booking_number }}</code></td>
                                <td>{{ $booking->customer_name }}</td>
                                <td>{{ $booking->court->name }}<div class="small text-secondary">{{ $booking->venue->name }}</div></td>
                                <td>{{ $booking->booking_date->format('d/m/Y') }} · {{ substr($booking->booking_time, 0, 5) }}–{{ \Illuminate\Support\Carbon::parse($booking->booking_time)->addHours($booking->duration_hours)->format('H:i') }}</td>
                                <td>{{ $booking->payment_uploaded_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="{{ route('bookings.proof', $booking) }}" target="_blank" rel="noopener" class="btn btn-sm btn-white">Lihat bukti</a>
                                        <form action="{{ route('bookings.verify', $booking) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="decision" value="approve">
                                            <button type="submit" class="btn btn-sm btn-success">Setujui</button>
                                        </form>
                                        <form action="{{ route('bookings.verify', $booking) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="decision" value="reject">
                                            <button type="submit" class="btn btn-sm btn-white text-danger">Tolak</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-secondary py-5">Tidak ada bukti pembayaran yang menunggu pemeriksaan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($canViewBookings)
        <div class="row g-4 mb-6">
            <div class="col-xl-8">
                <div class="card card-lg h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">Jadwal booking hari ini</h5>
                            <p class="text-secondary small mb-0">{{ $todayBookings->count() }} booking · {{ $bookingHoursToday }} jam lapangan</p>
                        </div>
                        <a href="{{ route('bookings.calendar') }}" class="btn btn-white btn-sm">Buka kalender</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-centered text-nowrap mb-0">
                            <thead>
                                <tr><th>Waktu</th><th>Lapangan / venue</th><th>Pelanggan</th><th>Status</th><th>Nilai</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($todayBookings as $booking)
                                    <tr>
                                        <td class="fw-semibold">{{ substr($booking->booking_time, 0, 5) }}–{{ \Illuminate\Support\Carbon::parse($booking->booking_time)->addHours($booking->duration_hours)->format('H:i') }}<div class="small text-secondary">{{ $booking->duration_hours }} jam</div></td>
                                        <td>{{ $booking->court->name }}<div class="small text-secondary">{{ $booking->venue->name }}</div></td>
                                        <td>{{ $booking->customer_name }}</td>
                                        <td><span class="badge {{ $statusClasses[$booking->status] ?? 'bg-secondary-subtle text-secondary-emphasis' }}">{{ $statusLabels[$booking->status] ?? $booking->status }}</span></td>
                                        <td>Rp{{ number_format($booking->amount, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-secondary py-5">Belum ada booking aktif untuk hari ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card card-lg h-100">
                    <div class="card-header">
                        <h5 class="mb-1">Aktivitas per lapangan</h5>
                        <p class="text-secondary small mb-0">Ringkasan pemakaian hari ini.</p>
                    </div>
                    <div class="card-body">
                        @forelse ($todayCourtSummary as $summary)
                            <div class="d-flex justify-content-between gap-3 py-3 border-bottom">
                                <div>
                                    <div class="fw-semibold">{{ $summary['court'] }}</div>
                                    <div class="small text-secondary">{{ $summary['venue'] }} · {{ $summary['count'] }} booking · {{ $summary['hours'] }} jam</div>
                                </div>
                                <div class="text-end small fw-semibold">Rp{{ number_format($summary['amount'], 0, ',', '.') }}</div>
                            </div>
                        @empty
                            <p class="text-secondary mb-0">Belum ada aktivitas booking hari ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
