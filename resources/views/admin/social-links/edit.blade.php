@extends('layouts.app')

@section('title', 'Edit Sosial Media')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-6">
        <h4 class="mb-0">Edit Sosial Media</h4>
        <a href="{{ route('social-links.index') }}" class="btn btn-white"><i class="ti ti-arrow-left me-1"></i> Kembali</a>
    </div>
    <div class="card card-lg"><div class="card-body">
        @include('admin.social-links._form', ['socialLink' => $socialLink, 'submitLabel' => 'Simpan Perubahan'])
    </div></div>
@endsection
