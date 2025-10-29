@extends('layouts.app')

@section('content')
<div class="container d-flex flex-column justify-content-center align-items-center vh-100">
    <h1 class="mb-3">Scan QR Code untuk Absensi</h1>
    <h2 class="mb-4">{{ $rapat->judul }}</h2>
    
    <div id="qr-code-container" class="p-4 bg-white rounded shadow-lg">
        {{-- Dummy QR Code for now. Will be replaced with a real one. --}}
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=dummy-token-{{ time() }}" alt="QR Code">
    </div>

    <div class="mt-4 text-center">
        <p class="lead">QR code akan diperbarui dalam</p>
        <h3 id="timer" class="font-weight-bold">30 detik</h3>
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

    function updateQrCode() {
        // This is where you would fetch the new QR code from the server.
        // For now, we'll just update the dummy image to show it's changing.
        const newDummyToken = `dummy-token-${new Date().getTime()}`;
        qrCodeContainer.innerHTML = `<img src="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=${newDummyToken}" alt="QR Code">`;
        console.log("QR Code updated with new token:", newDummyToken);
    }

    setInterval(updateTimer, 1000);
</script>
@endpush
