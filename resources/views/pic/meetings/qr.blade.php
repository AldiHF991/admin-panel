@extends('layouts.app')

@section('content')
<div class="container d-flex flex-column justify-content-center align-items-center vh-100">
    <h1 class="mb-3">Scan QR Code untuk Absensi</h1>
    <h2 class="mb-4">{{ $rapat->judul }}</h2>
    
    <div id="qr-code-container" class="p-4 bg-white rounded shadow-lg">
        {{-- Generate QR Code awal menggunakan simple-qrcode --}}
        {!! QrCode::size(400)->generate($rapat->current_qr_token ?? 'no-token-available') !!}
    </div>

    <div class="mt-4 text-center">
        <p class="lead">QR code akan diperbarui dalam</p>
        <h3 id="timer" class="font-weight-bold">30 detik</h3>
        <a href="{{ route('pic.meetings.index') }}" class="btn btn-secondary mt-3">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let timeLeft = 30;
    const timerElement = document.getElementById('timer');
    const qrCodeContainer = document.getElementById('qr-code-container');

    function updateTimer() {
        timerElement.textContent = `${timeLeft} detik`;
        if (timeLeft === 0) {
            timeLeft = 30; // Reset timer
            updateQrCode();
        } else {
            timeLeft--;
        }
    }

    async function updateQrCode() {
        try {
            // Fetch QR code baru (sebagai SVG) dari server
            const response = await fetch("{{ route('pic.meetings.getQrCodeSvg', $rapat->id_rapat) }}");
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const newQrCodeSvg = await response.text();
            qrCodeContainer.innerHTML = newQrCodeSvg;
            console.log("QR Code updated at: " + new Date().toLocaleTimeString());
        } catch (error) {
            console.error("Could not fetch new QR code:", error);
            qrCodeContainer.innerHTML = `<div class="alert alert-danger">Gagal memuat QR Code. Memuat ulang...</div>`;
        }
    }

    setInterval(updateTimer, 1000);
</script>
@endpush
