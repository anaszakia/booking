@extends('layouts.app')

@section('title', 'Master Venue')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h4 class="mb-0">Master Venue</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Venue</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('venues.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i> Tambah Venue</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card card-lg">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead>
                        <tr><th>Logo</th><th>Nama Venue</th><th>Alamat</th><th>Koordinat</th><th>Pembayaran</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($venues as $venue)
                            <tr>
                                <td>
                                    @if ($venue->logo_path)
                                        <img src="{{ minio_url($venue->logo_path) }}" alt="Logo {{ $venue->name }}" width="48" height="48" class="rounded object-fit-cover">
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td><strong>{{ $venue->name }}</strong></td>
                                <td>{{ $venue->address }}</td>
                                <td><code>{{ $venue->latitude }}, {{ $venue->longitude }}</code></td>
                                <td>
                                    <div>{{ $venue->qris_path ? 'QRIS tersedia' : 'QRIS belum diunggah' }}</div>
                                    <small class="text-muted">{{ $venue->bank_accounts_count }} rekening bank</small>
                                </td>
                                <td>
                                    <span class="badge {{ $venue->is_active ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">
                                        {{ $venue->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('venues.edit', $venue) }}" class="btn btn-sm btn-white" aria-label="Edit {{ $venue->name }}"><i class="ti ti-edit"></i></a>
                                        <form action="{{ route('venues.destroy', $venue) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-white text-danger" data-confirm="Yakin hapus venue {{ $venue->name }}?"><i class="ti ti-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-5">Belum ada data venue.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($venues->hasPages())
                <div class="p-4">{{ $venues->links() }}</div>
            @endif
        </div>
    </div>
@endsection
