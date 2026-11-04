@extends('layouts.app')

@section('title', 'Master Lapangan')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h4 class="mb-0">Master Lapangan</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Lapangan</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('courts.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Tambah Lapangan
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card card-lg">
        <div class="card-body p-0">
            <div class="p-4 border-bottom">
                <form action="{{ route('courts.index') }}" method="GET">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-8 col-lg-6">
                            <input type="search" name="search" class="form-control"
                                value="{{ request('search') }}" placeholder="Cari nama atau lokasi lapangan">
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary">Cari</button>
                            @if (request('search'))
                                <a href="{{ route('courts.index') }}" class="btn btn-white">Reset</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead>
                        <tr>
                            <th>Nama Lapangan</th>
                            <th>Venue</th>
                            <th>Lokasi</th>
                            <th>Tipe</th>
                            <th>Harga / Jam</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($courts as $court)
                            <tr>
                                <td>
                                    <strong>{{ $court->name }}</strong>
                                    @if ($court->address)
                                        <div class="small text-muted">{{ $court->address }}</div>
                                    @endif
                                </td>
                                <td>{{ $court->venue?->name ?? 'Belum ditentukan' }}</td>
                                <td>{{ $court->location }}</td>
                                <td>{{ $court->court_type === 'indoor' ? 'Indoor' : 'Outdoor' }}</td>
                                <td>Rp{{ number_format($court->price_per_hour, 0, ',', '.') }}</td>
                                <td>
                                    @if ($court->is_active)
                                        <span class="badge bg-success-subtle text-success-emphasis">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('courts.edit', $court) }}" class="btn btn-sm btn-white" aria-label="Edit {{ $court->name }}">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        <form action="{{ route('courts.destroy', $court) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-white text-danger"
                                                data-confirm="Yakin hapus lapangan {{ $court->name }}?">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    Belum ada data lapangan. Tambahkan lapangan pertama untuk memulai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($courts->hasPages())
                <div class="p-4">{{ $courts->links() }}</div>
            @endif
        </div>
    </div>
@endsection
