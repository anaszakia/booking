@extends('layouts.app')

@section('title', 'Master Sosial Media')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h4 class="mb-0">Master Sosial Media</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Sosial Media</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('social-links.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i> Tambah Sosial Media</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card card-lg">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead>
                        <tr><th>Nama</th><th>Platform</th><th>URL</th><th>Urutan</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($socialLinks as $socialLink)
                            <tr>
                                <td>{{ $socialLink->name }}</td>
                                <td>
                                    <span class="d-inline-flex align-items-center gap-2">
                                        <i class="{{ \App\Models\SocialLink::platformIcon($socialLink->platform) }} fs-5" aria-hidden="true"></i>
                                        {{ \App\Models\SocialLink::PLATFORMS[$socialLink->platform]['label'] ?? $socialLink->platform }}
                                    </span>
                                </td>
                                <td><a href="{{ $socialLink->url }}" target="_blank" rel="noopener noreferrer">{{ $socialLink->url }}</a></td>
                                <td>{{ $socialLink->sort_order }}</td>
                                <td>
                                    <span class="badge {{ $socialLink->is_active ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">
                                        {{ $socialLink->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('social-links.edit', $socialLink) }}" class="btn btn-sm btn-white" aria-label="Edit {{ $socialLink->name }}"><i class="ti ti-edit"></i></a>
                                        <form action="{{ route('social-links.destroy', $socialLink) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-white text-danger" data-confirm="Yakin hapus {{ $socialLink->name }}?"><i class="ti ti-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5">Belum ada tautan sosial media.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($socialLinks->hasPages())
                <div class="p-4">{{ $socialLinks->links() }}</div>
            @endif
        </div>
    </div>
@endsection
