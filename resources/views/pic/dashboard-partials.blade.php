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
                                            <tr data-rapat-id="{{ $rapat->id_rapat }}">
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
                                                            case 'overdue': $bgColor = 'dark'; break;
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
                                            <tr onclick="showMeetingDetail({{ $rapat }})" style="cursor: pointer;" data-rapat-id="{{ $rapat->id_rapat }}">
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
                                                            case 'overdue': $bgColor = 'dark'; break;
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
