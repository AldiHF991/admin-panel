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

    /* Highlight Animations */
    /* Highlight Animations */
    @keyframes highlightBlue {
        0% { background-color: rgba(13, 110, 253, 0.3); }
        100% { background-color: transparent; }
    }
    @keyframes highlightRed {
        0% { background-color: rgba(220, 53, 69, 0.3); }
        100% { background-color: transparent; }
    }
    @keyframes highlightGreen {
        0% { background-color: rgba(25, 135, 84, 0.3); }
        100% { background-color: transparent; }
    }
    @keyframes highlightYellow {
        0% { background-color: rgba(255, 193, 7, 0.3); }
        100% { background-color: transparent; }
    }
    @keyframes highlightGrey {
        0% { background-color: rgba(108, 117, 125, 0.3); }
        100% { background-color: transparent; }
    }
    .highlight-blue { animation: highlightBlue 2s ease-out; }
    .highlight-red { animation: highlightRed 2s ease-out; }
    .highlight-green { animation: highlightGreen 2s ease-out; }
    .highlight-yellow { animation: highlightYellow 2s ease-out; }
    .highlight-grey { animation: highlightGrey 2s ease-out; }
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
    <!-- Card: Menunggu Konfirmasi -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="bg-warning text-white p-3 rounded me-3">
                    <i class="bi bi-hourglass-split fs-3"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0">Menunggu Konfirmasi</h5>
                    <h3 class="fw-bold text-warning mt-1 count-up">{{ $totalMenunggu ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Card: Akan Datang -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="bg-primary text-white p-3 rounded me-3">
                    <i class="bi bi-calendar-event fs-3"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0">Akan Datang</h5>
                    <h3 class="fw-bold text-primary mt-1 count-up">{{ $totalAkanDatang ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Card: Total Pengajuan Saya -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="bg-success text-white p-3 rounded me-3">
                    <i class="bi bi-folder-check fs-3"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0">Total Pengajuan</h5>
                    <h3 class="fw-bold text-success mt-1 count-up">{{ $totalPengajuan ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<hr class="my-4">

<div id="dashboard-tables-container">
    @include('pic.dashboard-partials')
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
        // Listen for DashboardUpdate event
        console.log('Initializing Echo listener for DashboardUpdate...');

        if (window.Echo) {
            window.Echo.private('dashboard')
                .listen('.dashboard.update', (e) => {
                    console.log('✅ Dashboard update received:', e);
                    fetchDashboardTables(e.rapatId, e.statusId);
                })
                .error((error) => {
                    console.error('❌ Echo Error:', error);
                });
            console.log('Echo listener attached.');
        } else {
            console.error('❌ Laravel Echo is not loaded.');
        }

        // Manual Fetch Button for Debugging
        const debugBtn = document.createElement('button');
        debugBtn.textContent = 'Test Fetch Tables';
        debugBtn.className = 'btn btn-sm btn-outline-danger position-fixed bottom-0 end-0 m-3';
        debugBtn.style.zIndex = 9999;
        debugBtn.onclick = () => {
            console.log('Manual fetch triggered');
            fetchDashboardTables();
        };
        document.body.appendChild(debugBtn);

        function fetchDashboardTables(highlightId = null, statusId = null) {
            console.log('Fetching dashboard tables...');
            fetch('{{ route("pic.dashboard.tables") }}')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.statusText);
                    }
                    return response.text();
                })
                .then(html => {
                    console.log('✅ Dashboard tables updated from server.');
                    document.getElementById('dashboard-tables-container').innerHTML = html;
                    
                    // Re-initialize tooltips if any
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    });

                    // Apply Highlight Animation
                    if (highlightId && statusId) {
                        const row = document.querySelector(`tr[data-rapat-id="${highlightId}"]`);
                        if (row) {
                            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            
                            let highlightClass = '';
                            switch (parseInt(statusId)) {
                                case 1: highlightClass = 'highlight-green'; break; // Diterima
                                case 2: highlightClass = 'highlight-red'; break;   // Ditolak
                                case 3: highlightClass = 'highlight-yellow'; break; // Menunggu
                                case 4: highlightClass = 'highlight-blue'; break;  // Berlangsung
                                case 5: highlightClass = 'highlight-grey'; break;  // Selesai
                            }

                            if (highlightClass) {
                                row.classList.add(highlightClass);
                                setTimeout(() => {
                                    row.classList.remove(highlightClass);
                                }, 2000); // Remove after 2 seconds
                            }
                        }
                    }
                    // Re-initialize any scripts or event listeners for new content if necessary
                    // For example, if new tables have count-up elements, re-run animateCountUp
                    document.querySelectorAll('#dashboard-tables-container .count-up').forEach(el => {
                        animateCountUp(el); // Re-apply count-up animation
                    });
                })
                .catch(error => console.error('❌ Error fetching dashboard tables:', error));
        }

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
