@extends('layouts.app')

@section('title', 'Booking & Pembayaran')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h4 class="mb-0">Booking & Pembayaran</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Booking</li>
                </ol>
            </nav>
        </div>
    </div>

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

    <div class="card card-lg">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead>
                        <tr><th>Nomor Booking</th><th>Pemesan</th><th>Lapangan / Venue</th><th>Jadwal</th><th>Total</th><th>Status</th><th>Bukti & Aksi</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $booking)
                            @php
                                $statusLabels = [
                                    'pending_payment' => 'Menunggu pembayaran',
                                    'pending_verification' => 'Menunggu verifikasi',
                                    'confirmed' => 'Dikonfirmasi',
                                    'rejected' => 'Ditolak',
                                    'expired' => 'Kedaluwarsa',
                                ];
                            @endphp
                            <tr>
                                <td><code>{{ $booking->booking_number }}</code><div class="small text-muted">Dibuat {{ $booking->created_at->format('d/m/Y H:i') }}</div></td>
                                <td>{{ $booking->customer_name }}<div class="small text-muted">{{ $booking->customer_phone }}</div></td>
                                <td>{{ $booking->court->name }}<div class="small text-muted">{{ $booking->venue->name }}</div></td>
                                <td>{{ $booking->booking_date->format('d/m/Y') }} · {{ substr($booking->booking_time, 0, 5) }}</td>
                                <td>Rp{{ number_format($booking->amount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $booking->status === 'confirmed' ? 'bg-success-subtle text-success-emphasis' : ($booking->status === 'pending_verification' ? 'bg-warning-subtle text-warning-emphasis' : 'bg-secondary-subtle text-secondary-emphasis') }}">
                                        {{ $statusLabels[$booking->status] ?? $booking->status }}
                                    </span>
                                    @if ($booking->status === 'pending_payment')
                                        <div class="small text-muted">Tenggat {{ $booking->payment_expires_at->format('H:i') }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if ($booking->payment_proof_path)
                                        <a href="{{ route('bookings.proof', $booking) }}" class="js-preview-payment-proof" data-booking-number="{{ $booking->booking_number }}">Lihat bukti</a>
                                    @else
                                        <span class="text-muted">Belum ada</span>
                                    @endif
                                    @if ($booking->status === 'pending_verification')
                                        <div class="d-flex gap-1 mt-2">
                                            <form action="{{ route('bookings.verify', $booking) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="decision" value="approve">
                                                <button class="btn btn-sm btn-success" type="submit">Setujui</button>
                                            </form>
                                            <form action="{{ route('bookings.verify', $booking) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="decision" value="reject">
                                                <button class="btn btn-sm btn-white text-danger" type="submit">Tolak</button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-5">Belum ada booking.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($bookings->hasPages()) <div class="p-4">{{ $bookings->links() }}</div> @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.js-preview-payment-proof').forEach((link) => {
            link.addEventListener('click', (event) => {
                event.preventDefault();

                Swal.fire({
                    title: `Bukti pembayaran ${link.dataset.bookingNumber}`,
                    imageUrl: link.href,
                    imageAlt: `Bukti pembayaran ${link.dataset.bookingNumber}`,
                    imageWidth: 800,
                    imageHeight: 600,
                    imageClass: 'payment-proof-preview',
                    showCloseButton: true,
                    showConfirmButton: false,
                    width: 'min(900px, calc(100% - 32px))',
                    customClass: { popup: 'payment-proof-popup' },
                });
            });
        });
    </script>
    <style>
        .payment-proof-preview {
            width: auto !important;
            height: auto !important;
            max-width: 100% !important;
            max-height: 70vh !important;
            object-fit: contain;
        }
        .payment-proof-popup { max-width: calc(100vw - 32px) !important; }
    </style>
@endpush
