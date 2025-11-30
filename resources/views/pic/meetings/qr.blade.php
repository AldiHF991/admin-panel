@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card text-center">
                <div class="card-header">
                    <h3 class="card-title">QR Code Rapat: {{ $rapat->judul }}</h3>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        {!! $qrCode !!}
                    </div>
                    <p class="text-muted">Scan QR Code ini untuk melakukan absensi atau login sebagai tamu.</p>
                    
                    <div class="mt-3">
                        <a href="{{ route('meetings.getQrCodeSvg', $rapat->id_rapat) }}" class="btn btn-primary" download="qrcode-{{ $rapat->id_rapat }}.svg">
                            <i class="fas fa-download"></i> Download QR Code
                        </a>
                        <a href="{{ route('pic.meetings.show', $rapat->id_rapat) }}" class="btn btn-secondary">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
