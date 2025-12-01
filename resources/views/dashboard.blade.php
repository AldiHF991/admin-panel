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

    /* Animasi popup untuk kartu statistik */
    .stat-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-8px) scale(1.03);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
    }
</style>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body text-center">
        <h5 id="realtime-clock-date" class="mb-1"></h5>
        <div class="d-flex justify-content-center align-items-center">
            <h3 id="realtime-clock-time" class="fw-bold text-primary mb-0 me-2"></h3>
            <span class="badge bg-primary">WIB</span>
        </div>
    </div>
</div>

<hr class="my-4">


<div class="row g-4 mb-4">
    <!-- Card: Total Rapat -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card">
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
        <div class="card border-0 shadow-sm stat-card">
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
        <div class="card border-0 shadow-sm stat-card">
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

<hr class="my-4">

<div class="row">
    <!-- Kolom Kiri: Rapat Baru Dibuat -->
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Rapat Baru Dibuat (3 Hari Terakhir)</h5>
                <a href="{{ route('reports.newlyCreated') }}" target="_blank" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-arrows-fullscreen me-1"></i> Layar Penuh
                </a>
            </div>
            <div class="card-body p-0">
                <div class="recent-activity-scroll">
                    @forelse ($rapatBaruDibuat->groupBy(fn($item) => $item->created_at->format('Y-m-d')) as $tanggal => $rapats)
                        <div class="mb-4">
                            <h6 class="fw-bold text-success border-bottom pb-2 mb-3">
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
                            <p class="mb-0">Tidak ada rapat yang dibuat dalam 3 hari terakhir.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Aktivitas Rapat Terkini -->
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Aktivitas Rapat (Jadwal Terdekat)</h5>
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
    </div>
</div>

</div>

<!-- Modal Detail Rapat -->
<div class="modal fade" id="meetingDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="modalJudul">Detail Rapat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-bold">Judul Rapat</label>
                    <h5 id="modalJudulRapat" class="fw-bold text-dark"></h5>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="small text-muted text-uppercase fw-bold">Tanggal</label>
                        <p id="modalTanggal" class="mb-0 fw-medium"></p>
                    </div>
                    <div class="col-6">
                        <label class="small text-muted text-uppercase fw-bold">Waktu</label>
                        <p id="modalWaktu" class="mb-0 fw-medium"></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="small text-muted text-uppercase fw-bold">Ruangan</label>
                        <p id="modalRuangan" class="mb-0 fw-medium"></p>
                    </div>
                    <div class="col-6">
                        <label class="small text-muted text-uppercase fw-bold">Pengaju</label>
                        <p id="modalPengaju" class="mb-0 fw-medium"></p>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-bold">Deskripsi</label>
                    <p id="modalDeskripsi" class="mb-0 text-secondary"></p>
                </div>
                <div class="mb-0">
                    <label class="small text-muted text-uppercase fw-bold">Status</label>
                    <div><span id="modalStatus" class="badge rounded-pill"></span></div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
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
        document.getElementById('realtime-clock-time').textContent = now.toLocaleTimeString('id-ID', optionsTime);
    }

    setInterval(updateClock, 1000);
    updateClock(); // Initial call

    // Fungsi untuk menampilkan modal detail rapat
    window.showMeetingDetail = function(rapat) {
        document.getElementById('modalJudulRapat').textContent = rapat.judul;
        
        // Format Tanggal
        const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const date = new Date(rapat.tanggal);
        document.getElementById('modalTanggal').textContent = date.toLocaleDateString('id-ID', dateOptions);

        // Format Waktu
        const start = rapat.waktu_start.substring(0, 5);
        const end = rapat.waktu_end ? rapat.waktu_end.substring(0, 5) : '?';
        document.getElementById('modalWaktu').textContent = `${start} - ${end} WIB`;

        document.getElementById('modalRuangan').textContent = rapat.room ? rapat.room.room : 'N/A';
        document.getElementById('modalPengaju').textContent = rapat.pengaju ? rapat.pengaju.name : 'N/A';
        document.getElementById('modalDeskripsi').textContent = rapat.desc || '-';

        // Status Badge
        const statusSpan = document.getElementById('modalStatus');
        const statusText = rapat.status ? rapat.status.status_rapat : 'N/A';
        statusSpan.textContent = statusText;
        
        let statusClass = 'bg-secondary';
        switch(statusText.toLowerCase()) {
            case 'diterima': statusClass = 'bg-success'; break;
            case 'ditolak': statusClass = 'bg-danger'; break;
            case 'menunggu': 
            case 'menunggu persetujuan': statusClass = 'bg-warning'; break;
            case 'berlangsung': statusClass = 'bg-primary'; break;
        }
        statusSpan.className = `badge rounded-pill ${statusClass}`;

        // Show Modal
        const modal = new bootstrap.Modal(document.getElementById('meetingDetailModal'));
        modal.show();
    };
</script>
@endpush
