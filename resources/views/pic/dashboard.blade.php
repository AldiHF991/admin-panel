@extends('layouts.app')

@section('title', 'Dashboard PIC')
@section('page-title', 'Dashboard')

@section('content')

<style>
    .recent-activity-scroll {
        max-height: 450px;
        overflow-y: auto;
        padding: 1.5rem;
    }

    /* Styling untuk scrollbar modern */
    .recent-activity-scroll::-webkit-scrollbar {
        width: 4px;
        transition: width 0.3s ease;
    }

    .recent-activity-scroll:hover::-webkit-scrollbar {
        width: 8px;
    }

    .recent-activity-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .recent-activity-scroll::-webkit-scrollbar-thumb {
        background: #0d6efd;
        border-radius: 10px;
        transition: background-color 0.3s ease;
    }

    .recent-activity-scroll:hover::-webkit-scrollbar-thumb {
        background-color: #0a58ca;
    }

    .table-hover > tbody > tr {
        transition: background-color 0.2s ease-in-out;
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

<div class="row">
    <!-- Kolom Kiri: Rapat Baru Dibuat -->
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Rapat Baru Dibuat (3 Hari Terakhir)</h5>
                <a href="{{ route('pic.reports.newlyCreated') }}" target="_blank" class="btn btn-sm btn-outline-success">
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
                                                        $bgColor = 'secondary';
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
                <a href="{{ route('pic.reports.recentActivity') }}" target="_blank" class="btn btn-sm btn-outline-primary">
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
                                                        $bgColor = 'secondary';
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

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
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
    });
</script>
@endpush
