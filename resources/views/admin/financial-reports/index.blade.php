@extends('layouts.app')

@section('title', 'Rekap Keuangan')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h4 class="mb-0">Rekap Keuangan</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Rekap Keuangan</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card card-lg mb-4">
        <div class="card-body">
            <form action="{{ route('financial-reports.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-sm-5 col-lg-4">
                    <label class="form-label" for="from">Dari tanggal</label>
                    <input class="form-control" id="from" name="from" type="date" value="{{ $from }}" required>
                </div>
                <div class="col-sm-5 col-lg-4">
                    <label class="form-label" for="to">Sampai tanggal</label>
                    <input class="form-control" id="to" name="to" type="date" value="{{ $to }}" required>
                </div>
                <div class="col-sm-2">
                    <button class="btn btn-primary w-100" type="submit"><i class="ti ti-filter me-1"></i> Terapkan</button>
                </div>
                @error('to')
                    <div class="col-12"><div class="text-danger small">{{ $message }}</div></div>
                @enderror
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card card-lg h-100">
                <div class="card-body">
                    <div class="text-muted mb-2"><i class="ti ti-cash me-1"></i> Total pemasukan</div>
                    <h3 class="mb-0">Rp{{ number_format($summary['income'], 0, ',', '.') }}</h3>
                    <small class="text-muted">Booking yang sudah dikonfirmasi</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-lg h-100">
                <div class="card-body">
                    <div class="text-muted mb-2"><i class="ti ti-receipt me-1"></i> Transaksi terkonfirmasi</div>
                    <h3 class="mb-0">{{ number_format($summary['transactions'], 0, ',', '.') }}</h3>
                    <small class="text-muted">Pada periode yang dipilih</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-lg h-100">
                <div class="card-body">
                    <div class="text-muted mb-2"><i class="ti ti-chart-line me-1"></i> Rata-rata transaksi</div>
                    <h3 class="mb-0">Rp{{ number_format($summary['average'], 0, ',', '.') }}</h3>
                    <small class="text-muted">Pemasukan per booking terkonfirmasi</small>
                </div>
            </div>
        </div>
    </div>

    @if ($venueSummaries->isNotEmpty())
        <div class="card card-lg mb-4">
            <div class="card-header">
                <h5 class="mb-0">Pemasukan per venue</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead><tr><th>Venue</th><th>Transaksi</th><th>Total pemasukan</th></tr></thead>
                        <tbody>
                            @foreach ($venueSummaries as $venueSummary)
                                <tr>
                                    <td>{{ $venueSummary->venue_name }}</td>
                                    <td>{{ number_format($venueSummary->transaction_count, 0, ',', '.') }}</td>
                                    <td>Rp{{ number_format($venueSummary->income, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="card card-lg">
        <div class="card-header">
            <div>
                <h5 class="mb-1">Transaksi terkonfirmasi</h5>
                <small class="text-muted">Periode {{ \Illuminate\Support\Carbon::parse($from)->format('d/m/Y') }}–{{ \Illuminate\Support\Carbon::parse($to)->format('d/m/Y') }}</small>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead>
                        <tr><th>Waktu konfirmasi</th><th>Nomor booking</th><th>Pemesan</th><th>Lapangan / Venue</th><th>Jadwal main</th><th>Pemasukan</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $booking)
                            <tr>
                                <td>{{ $booking->verified_at->format('d/m/Y H:i') }}</td>
                                <td><code>{{ $booking->booking_number }}</code></td>
                                <td>{{ $booking->customer_name }}</td>
                                <td>{{ $booking->court->name }}<div class="small text-muted">{{ $booking->venue->name }}</div></td>
                                <td>{{ $booking->booking_date->format('d/m/Y') }} · {{ substr($booking->booking_time, 0, 5) }}</td>
                                <td>Rp{{ number_format($booking->amount, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5">Belum ada pemasukan terkonfirmasi pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($transactions->hasPages())
                <div class="p-4">{{ $transactions->links() }}</div>
            @endif
        </div>
    </div>
@endsection
