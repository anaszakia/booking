@extends('layouts.app')

@section('title', 'Master Lokasi & Peta')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h4 class="mb-0">Master Lokasi & Peta</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Lokasi & Peta</li>
                </ol>
            </nav>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card card-lg">
        <div class="card-body">
            <form class="master-form" action="{{ route('venue-settings.update', $settings) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="name" class="form-label">Nama venue <span class="text-danger">*</span></label>
                    <input id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $settings->name) }}" maxlength="120" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-4">
                    <label for="address" class="form-label">Alamat <span class="text-danger">*</span></label>
                    <textarea id="address" name="address" rows="3"
                        class="form-control @error('address') is-invalid @enderror" required>{{ old('address', $settings->address) }}</textarea>
                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <label for="latitude" class="form-label">Latitude <span class="text-danger">*</span></label>
                        <input id="latitude" name="latitude" type="number" step="any" min="-90" max="90"
                            class="form-control @error('latitude') is-invalid @enderror"
                            value="{{ old('latitude', $settings->latitude) }}" required>
                        @error('latitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-4">
                        <label for="longitude" class="form-label">Longitude <span class="text-danger">*</span></label>
                        <input id="longitude" name="longitude" type="number" step="any" min="-180" max="180"
                            class="form-control @error('longitude') is-invalid @enderror"
                            value="{{ old('longitude', $settings->longitude) }}" required>
                        @error('longitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-4">
                        <label for="map_zoom" class="form-label">Zoom peta (1–19) <span class="text-danger">*</span></label>
                        <input id="map_zoom" name="map_zoom" type="number" min="1" max="19"
                            class="form-control @error('map_zoom') is-invalid @enderror"
                            value="{{ old('map_zoom', $settings->map_zoom) }}" required>
                        @error('map_zoom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <p class="form-text">Latitude dan longitude mengatur titik marker serta tautan Google Maps pada footer publik. Pastikan koordinat menunjuk ke lokasi venue yang benar.</p>
                <button type="submit" class="btn btn-primary">Simpan Lokasi</button>
            </form>
        </div>
    </div>
@endsection
