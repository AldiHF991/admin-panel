@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

<style>
    .recent-activity-scroll {
        max-height: 450px; /* Anda bisa menyesuaikan tinggi maksimal ini */
        overflow-y: auto;
        padding: 1.5rem;
    }

    /* Styling untuk scrollbar modern (berbasis WebKit: Chrome, Safari, Edge) */
    .recent-activity-scroll::-webkit-scrollbar {
        width: 4px; /* Lebih kecil secara default */
        transition: width 0.3s ease; /* Transisi lebih halus */
    }

    .recent-activity-scroll:hover::-webkit-scrollbar {
        width: 8px; /* Menjadi lebih besar saat di-hover */
    }

    .recent-activity-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .recent-activity-scroll::-webkit-scrollbar-thumb {
        background: #0d6efd;
        border-radius: 10px;
        transition: background-color 0.3s ease; /* Transisi warna thumb */
    }

    .recent-activity-scroll:hover::-webkit-scrollbar-thumb {
        background-color: #0a58ca; /* Warna thumb sedikit lebih gelap saat hover */
    }

    /* Transisi halus untuk hover pada baris tabel */
    .table-hover > tbody > tr {
        transition: background-color 0.2s ease-in-out;
    }
</style>

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
                    <h3 class="fw-bold text-primary mt-1 count-up">{{ $totalRapat ?? 0 }}</h3>
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
                    <h3 class="fw-bold text-success mt-1 count-up">{{ $totalPengguna ?? 0 }}</h3>
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
                    <h3 class="fw-bold text-warning mt-1 count-up">{{ $totalCabang ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Aktivitas Rapat (3 Hari Terakhir) -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Aktivitas Rapat (3 Hari Terakhir)</h5>
        <a href="{{ route('reports.recentActivity') }}" target="_blank" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-arrows-fullscreen me-1"></i> Layar Penuh
        </a>
    </div>
    <div class="card-body p-0">
        <div class="recent-activity-scroll">
            @forelse ($rapatTigaHariTerakhir->groupBy('tanggal') as $tanggal => $rapats)
                <div class="mb-4">
                    <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                        {{ \Carbon\Carbon::parse($tanggal)->isoFormat('dddd, D MMMM Y') }}
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 35%;">Judul Rapat</th>
                                    <th scope="col" style="width: 20%;">Waktu</th>
                                    <th scope="col" style="width: 15%;">Ruangan</th>
                                    <th scope="col" style="width: 15%;">Pengaju</th>
                                    <th scope="col" style="width: 15%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rapats as $rapat)
                                    <tr>
                                        <td class="fw-medium">{{ $rapat->judul }}</td>
                                        <td>{{ \Carbon\Carbon::parse($rapat->waktu_start)->format('H:i') }} - {{ \Carbon\Carbon::parse($rapat->waktu_end)->format('H:i') }} WIB</td>
                                        <td>{{ $rapat->room->room ?? 'N/A' }}</td>
                                        <td>{{ $rapat->pengaju->name ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $statusText = $rapat->status->status_rapat ?? 'N/A';
                                                $bgColor = 'secondary'; // Warna default
                                                switch (strtolower($statusText)) {
                                                    case 'diterima': $bgColor = 'success'; break;
                                                    case 'ditolak': $bgColor = 'danger'; break;
                                                    case 'menunggu':
                                                    case 'menunggu persetujuan': $bgColor = 'warning'; break;
                                                    case 'berlangsung': $bgColor = 'primary'; break;
                                                }
                                            @endphp
                                            <span class="badge rounded-pill bg-{{ $bgColor }}">
                                                {{ $statusText }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">
                    <p class="mb-0">Tidak ada aktivitas rapat dalam 3 hari terakhir.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Fungsi untuk animasi count-up yang modern
        const animateCountUp = (el) => {
            const target = parseInt(el.dataset.target || el.textContent, 10);
            const duration = 1500; // Durasi animasi dalam milidetik (1.5 detik)
            let startTimestamp = null;
            el.textContent = '0'; // Mulai dari 0

            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                
                // Efek ease-out (melambat di akhir) untuk kesan lebih halus
                const easedProgress = 1 - Math.pow(1 - progress, 3);
                
                el.textContent = Math.floor(easedProgress * target);

                if (progress < 1) {
                    window.requestAnimationFrame(step);
                } else {
                    // Pastikan angka akhir sesuai target setelah animasi selesai
                    el.textContent = target.toLocaleString('id-ID');
                }
            };
            window.requestAnimationFrame(step);
        };

        // Terapkan animasi ke semua elemen dengan class 'count-up'
        document.querySelectorAll('.count-up').forEach(el => {
            animateCountUp(el);
        });
    });

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
