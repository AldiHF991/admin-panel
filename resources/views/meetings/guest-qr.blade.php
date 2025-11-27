@extends('layouts.app')

@section('content')
<div class="container d-flex flex-column justify-content-center align-items-center vh-100">
    <h1 class="mb-3">Pindai untuk Mode Tamu</h1>
    <h2 class="mb-4 text-center">{{ $rapat->judul }}</h2>

    <div id="qr-code-container" class="p-4 bg-white rounded shadow-lg">
        {{-- Generate QR Code yang mengarah ke halaman guest login --}}
        {!! QrCode::size(400)->generate($guestUrl) !!}
    </div>

    <div class="mt-4 text-center">
        <p class="lead">Gunakan kamera ponsel untuk membuka halaman absensi tamu.</p>
        <a href="{{ $guestUrl }}" target="_blank" class="btn btn-primary mt-2">
            <i class="bi bi-box-arrow-up-right me-2"></i>Buka Halaman di Tab Baru
        </a>
    </div>
</div>
@endsection

{{-- Tidak memerlukan script tambahan karena QR code statis --}}