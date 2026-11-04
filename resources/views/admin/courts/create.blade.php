@extends('layouts.app')

@section('title', 'Tambah Lapangan')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h4 class="mb-0">Tambah Lapangan</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('courts.index') }}">Lapangan</a></li>
                    <li class="breadcrumb-item active">Tambah</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('courts.index') }}" class="btn btn-white"><i class="ti ti-arrow-left me-1"></i> Kembali</a>
    </div>

    <div class="card card-lg">
        <div class="card-body">
            @include('admin.courts._form', ['court' => null, 'submitLabel' => 'Simpan Lapangan'])
        </div>
    </div>
@endsection
