@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body text-center">
        <h5 id="realtime-clock-date" class="mb-1"></h5>
        <h3 id="realtime-clock-time" class="fw-bold text-primary mb-0"></h3>
    </div>
</div>


<div class="row g-4 mb-4">
    <!-- Card: Total Rapat -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="bg-primary text-white p-3 rounded me-3">
                    <i class="bi bi-calendar3 fs-3"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0">Total Rapat</h5>
                    <h3 class="fw-bold text-primary mt-1">{{ $totalRapat ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Card: Total Pengguna -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="bg-success text-white p-3 rounded me-3">
                    <i class="bi bi-people-fill fs-3"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0">Total Pengguna</h5>
                    <h3 class="fw-bold text-success mt-1">{{ $totalPengguna ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Card: Total Cabang -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="bg-warning text-white p-3 rounded me-3">
                    <i class="bi bi-building fs-3"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0">Total Cabang</h5>
                    <h3 class="fw-bold text-warning mt-1">{{ $totalCabang ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rapat Akan Datang -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0">Jadwal Rapat Terdekat</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th scope="col">Judul Rapat</th>
                        <th scope="col">Tanggal & Waktu</th>
                        <th scope="col">Ruangan</th>
                        <th scope="col">Pengaju</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rapatAkanDatang as $rapat)
                        <tr>
                            <td class="fw-medium">{{ $rapat->judul }}</td>
                            <td>{{ \Carbon\Carbon::parse($rapat->tanggal)->isoFormat('dddd, D MMMM Y') }} | {{ \Carbon\Carbon::parse($rapat->waktu_start)->format('H:i') }} - {{ \Carbon\Carbon::parse($rapat->waktu_end)->format('H:i') }} WIB</td>
                            <td>{{ $rapat->room->room ?? 'N/A' }}</td>
                            <td>{{ $rapat->pengaju->name ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $statusText = $rapat->status->status_rapat ?? 'N/A';
                                    $bgColor = 'secondary'; // Warna default
                                    switch (strtolower($statusText)) {
                                        case 'diterima':
                                            $bgColor = 'success';
                                            break;
                                        case 'ditolak':
                                            $bgColor = 'danger';
                                            break;
                                        case 'menunggu':
                                        case 'menunggu persetujuan':
                                            $bgColor = 'warning';
                                            break;
                                        case 'berlangsung':
                                            $bgColor = 'primary';
                                            break;
                                    }
                                @endphp
                                <span class="badge rounded-pill bg-{{ $bgColor }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Tidak ada rapat yang akan datang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function updateClock() {
        const now = new Date();
        const optionsDate = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            timeZone: 'Asia/Jakarta'
        };
        const optionsTime = {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
            timeZone: 'Asia/Jakarta'
        };

        document.getElementById('realtime-clock-date').textContent = now.toLocaleDateString('id-ID', optionsDate);
        document.getElementById('realtime-clock-time').textContent = now.toLocaleTimeString('id-ID', optionsTime) + ' WIB';
    }

    setInterval(updateClock, 1000);
    updateClock(); // Initial call
</script>
@endpush
