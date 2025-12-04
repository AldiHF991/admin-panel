@extends('layouts.app')

@section('content')
{{-- Elemen untuk Loading Overlay --}}
<div id="loading-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1060; justify-content: center; align-items: center;">
    <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<div class="container mt-4">

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">List Rapat</h5>
            <a href="{{ route('pic.meetings.create') }}" class="btn btn-light btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Tambah Rapat
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('pic.meetings.index') }}" method="GET" id="filter-form">
                <div class="row mb-3 g-2">
                    <div class="col-md-12">
                        <label for="search-input" class="form-label visually-hidden">Cari Judul Rapat</label>
                        <input type="text" name="search" id="search-input" class="form-control" placeholder="Cari berdasarkan judul rapat..." value="{{ request('search') }}">
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover mt-3">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>
                                <a href="{{ route('pic.meetings.index', array_merge(request()->query(), ['sort' => 'judul', 'direction' => (request('sort') === 'judul' && request('direction') === 'asc') ? 'desc' : 'asc'])) }}" class="text-decoration-none text-black">
                                    Judul
                                    @if (request('sort') === 'judul')
                                        <i class="bi {{ request('direction') === 'asc' ? 'bi-sort-alpha-down' : 'bi-sort-alpha-up' }}"></i>
                                    @else
                                        <i class="bi bi-sort-alpha-down text-muted small"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Cabang</th>
                            <th>Ruangan</th>
                            <th>
                                <a href="{{ route('pic.meetings.index', array_merge(request()->query(), ['sort' => 'tanggal', 'direction' => (request('sort') === 'tanggal' && request('direction') === 'asc') ? 'desc' : 'asc'])) }}" class="text-decoration-none text-black">
                                    Tanggal
                                    @if (request('sort') === 'tanggal')
                                        <i class="bi {{ request('direction') === 'asc' ? 'bi-sort-numeric-down' : 'bi-sort-numeric-up' }}"></i>
                                    @else
                                        <i class="bi bi-sort-numeric-down text-muted small"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('pic.meetings.index', array_merge(request()->query(), ['sort' => 'waktu_start', 'direction' => (request('sort') === 'waktu_start' && request('direction') === 'asc') ? 'desc' : 'asc'])) }}" class="text-decoration-none text-black">
                                    Waktu
                                    @if (request('sort') === 'waktu_start')
                                        <i class="bi {{ request('direction') === 'asc' ? 'bi-sort-numeric-down' : 'bi-sort-numeric-up' }}"></i>
                                    @else
                                        <i class="bi bi-sort-numeric-down text-muted small"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('pic.meetings.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => (request('sort') === 'status' && request('direction') === 'asc') ? 'desc' : 'asc'])) }}" class="text-decoration-none text-black">
                                    Status
                                    @if (request('sort') === 'status')
                                        <i class="bi {{ request('direction') === 'asc' ? 'bi-sort-down' : 'bi-sort-up' }}"></i>
                                    @else
                                        <i class="bi bi-sort-down text-muted small"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($meetings as $rapat)
                            <tr id="rapat-row-{{ $rapat->id_rapat }}" class="{{ session('highlight_id') == $rapat->id_rapat ? 'highlight-row' : '' }}">
                                <td>{{ $meetings->firstItem() + $loop->index }}</td>
                                <td>{{ $rapat->judul }}</td>
                                <td>{{ $rapat->cabang ? $rapat->cabang->cabang : 'N/A' }}</td>
                                <td>{{ $rapat->room ? $rapat->room->room : 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($rapat->tanggal)->translatedFormat('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($rapat->waktu_start)->format('H:i') }} - {{ \Carbon\Carbon::parse($rapat->waktu_end)->format('H:i') }}</td>
                                <td>
                                    @php
                                        $statusText = $rapat->status ? $rapat->status->status_rapat : 'N/A';
                                        $statusClass = 'bg-secondary';
                                        switch (strtolower($statusText)) {
                                            case 'diterima': $statusClass = 'bg-success'; break;
                                            case 'ditolak': $statusClass = 'bg-danger'; break;
                                            case 'menunggu': $statusClass = 'bg-warning text-dark'; break;
                                            case 'berlangsung': $statusClass = 'bg-primary'; break;
                                            case 'selesai': $statusClass = 'bg-dark'; break;
                                        }
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton{{ $rapat->id_rapat }}" data-bs-toggle="dropdown" aria-expanded="false" data-bs-boundary="viewport" data-bs-popper-config='{"strategy":"fixed"}'>Aksi</button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $rapat->id_rapat }}">
                                            <li><a class="dropdown-item btn-detail-meeting" href="#" data-id="{{ $rapat->id_rapat }}" data-bs-toggle="modal" data-bs-target="#detailRapatModal"><i class="bi bi-eye me-2"></i>Detail</a></li>
                                            <li><a class="dropdown-item btn-upload-file-action" href="#" data-id="{{ $rapat->id_rapat }}" data-bs-toggle="modal" data-bs-target="#uploadFileModal"><i class="bi bi-upload me-2"></i>Upload File</a></li>
                                            <li><a class="dropdown-item" href="{{ route('pic.meetings.absensi', $rapat->id_rapat) }}" target="_blank"><i class="bi bi-person-check me-2"></i>Absensi</a></li>
                                            @if($rapat->id_status == 4)
                                                <li><a class="dropdown-item" href="{{ route('pic.meetings.qr', $rapat->id_rapat) }}" target="_blank"><i class="bi bi-qr-code me-2"></i>QR Code Absensi</a></li>
                                                <li><a class="dropdown-item" href="{{ route('pic.meetings.guestQr', $rapat->id_rapat) }}" target="_blank"><i class="bi bi-person-square me-2"></i>QR Mode Tamu</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button type="button" class="dropdown-item text-success btn-finish-meeting" 
                                                            data-rapat-id="{{ $rapat->id_rapat }}" 
                                                            data-rapat-judul="{{ $rapat->judul }}">
                                                        <i class="bi bi-check-circle me-2"></i>Selesai
                                                    </button>
                                                </li>
                                            @endif
                                            @if($rapat->id_status == 3)
                                                <li>
                                                    <form action="{{ route('pic.meetings.destroy', $rapat->id_rapat) }}" method="POST" class="d-inline delete-meeting-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Anda yakin ingin membatalkan pengajuan rapat ini?')"><i class="bi bi-trash me-2"></i>Batalkan</button>
                                                    </form>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    Belum ada data rapat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $meetings->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

@push('modals')
{{-- Add Rapat Modal --}}
<div class="modal fade" id="addRapatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('pic.meetings.store') }}" method="POST" class="modal-content" id="addRapatForm" novalidate enctype="multipart/form-data">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah Rapat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="addRapatBody">
                <div class="mb-3">
                    <label for="add_judul" class="form-label">Judul Rapat</label>
                    <input type="text" class="form-control" id="add_judul" name="judul" required>
                    <div class="invalid-feedback">Judul rapat tidak boleh kosong.</div>
                </div>
                <div class="mb-3">
                    <label for="add_tanggal" class="form-label">Tanggal</label>
                    <input type="date" class="form-control date-input" id="add_tanggal" name="tanggal" required>
                    <div class="invalid-feedback">Tanggal tidak boleh kosong.</div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="add_waktu_start" class="form-label">Waktu Mulai</label>
                        <input type="time" class="form-control time-input" id="add_waktu_start" name="waktu_start" required>
                        <div class="invalid-feedback">Waktu mulai tidak boleh kosong atau di masa lampau.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="add_waktu_end" class="form-label">Waktu Selesai</label>
                        <input type="time" class="form-control time-input" id="add_waktu_end" name="waktu_end">
                        <div class="invalid-feedback">Waktu selesai harus setelah waktu mulai.</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="add_id_cabang" class="form-label">Cabang</label>
                        <select class="form-select" id="add_id_cabang" name="id_cabang" required>
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($cabangs as $cabang)
                                <option value="{{ $cabang->id }}">{{ $cabang->cabang }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Silakan pilih cabang.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="add_id_room" class="form-label">Ruangan</label>
                        <select class="form-select room-select" id="add_id_room" name="id_room" required disabled>
                            <option value="">-- Pilih Ruangan --</option>
                        </select>
                        <small class="form-text text-danger room-help-text">Pilih tanggal & waktu mulai dulu.</small>
                        <div class="invalid-feedback">Silakan pilih ruangan yang tersedia.</div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="add_desc" class="form-label">Deskripsi (Opsional)</label>
                    <textarea class="form-control" id="add_desc" name="desc" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label for="add_files" class="form-label">Dokumen Pendukung (Opsional)</label>
                    <input class="form-control" type="file" id="add_files" name="files[]" multiple>
                    <div id="add-files-error" class="invalid-feedback" style="display: none;"></div>
                    <small class="form-text text-muted">Bisa pilih lebih dari satu file (Ctrl+Klik). Tipe: jpg, png, pdf, doc, docx, ppt, pptx, txt. Maks 5MB/file.</small>
                    <ul class="list-group mt-2" id="add-files-list"></ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Detail Rapat Modal --}}
<div class="modal fade" id="detailRapatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Detail Rapat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailRapatBody">
                <div class="text-center py-5" id="detail-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div id="detail-content" style="display: none;">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 200px">Judul</th>
                            <td id="detail-judul"></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td><span class="badge" id="detail-status"></span></td>
                        </tr>
                        <tr id="row-rejection-note" style="display: none;">
                            <th class="text-danger">Alasan Penolakan</th>
                            <td id="detail-rejection-note" class="text-danger"></td>
                        </tr>
                        <tr>
                            <th>Cabang</th>
                            <td id="detail-cabang"></td>
                        </tr>
                        <tr>
                            <th>Ruangan</th>
                            <td id="detail-room"></td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td id="detail-tanggal"></td>
                        </tr>
                        <tr>
                            <th>Waktu</th>
                            <td id="detail-waktu"></td>
                        </tr>
                        <tr>
                            <th>Deskripsi</th>
                            <td id="detail-desc"></td>
                        </tr>
                        <tr>
                            <th>Pengaju</th>
                            <td id="detail-pengaju"></td>
                        </tr>
                        <tr>
                            <th>Dokumen / File</th>
                            <td>
                                <ul class="list-group mb-2" id="detail-files-list">
                                    <!-- Files will be populated here -->
                                </ul>
                            </td>
                        </tr>
                    </table>
                    <div class="mt-3 text-end">
                        <a href="#" id="btn-lihat-absensi" class="btn btn-info">Lihat Absensi</a>
                        <a href="#" id="btn-lihat-qr" class="btn btn-dark">Lihat QR Code</a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Upload File Modal --}}
<div class="modal fade" id="uploadFileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-cloud-upload me-2"></i>Upload File Rapat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="mb-4 text-center">
                    <h5 id="upload-modal-judul" class="fw-bold text-primary mb-1">Loading...</h5>
                    <p class="text-muted small">Kelola dokumen rapat Anda di sini. Maksimal 20MB per file.</p>
                </div>

                <div class="row g-3">
                    {{-- Kategori 1: Materi --}}
                    <div class="col-md-6">
                        <div class="card h-100 border-primary shadow-sm upload-zone position-relative" data-category="1" style="cursor: pointer; transition: all 0.2s;">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-file-earmark-text me-2"></i>Materi</span>
                                <span class="badge bg-white text-primary rounded-pill" id="count-materi">0</span>
                            </div>
                            <div class="card-body p-2 d-flex flex-column">
                                <ul class="list-group list-group-flush mb-2 flex-grow-1" id="list-materi" style="min-height: 50px;">
                                    <li class="list-group-item text-center text-muted small fst-italic py-3">Belum ada file.</li>
                                </ul>
                                <div class="p-3 text-center border rounded bg-light dashed-border mt-auto">
                                    <i class="bi bi-cloud-arrow-up text-primary fs-3"></i>
                                    <p class="small mb-0 text-muted">Drag & Drop atau Klik di sini</p>
                                </div>
                                <input type="file" class="d-none file-input" data-category="1" multiple>
                            </div>
                        </div>
                    </div>

                    {{-- Kategori 2: Notulensi --}}
                    <div class="col-md-6">
                        <div class="card h-100 border-success shadow-sm upload-zone position-relative" data-category="2" style="cursor: pointer; transition: all 0.2s;">
                            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-journal-text me-2"></i>Notulensi</span>
                                <span class="badge bg-white text-success rounded-pill" id="count-notulensi">0</span>
                            </div>
                            <div class="card-body p-2 d-flex flex-column">
                                <ul class="list-group list-group-flush mb-2 flex-grow-1" id="list-notulensi" style="min-height: 50px;">
                                    <li class="list-group-item text-center text-muted small fst-italic py-3">Belum ada file.</li>
                                </ul>
                                <div class="p-3 text-center border rounded bg-light dashed-border mt-auto">
                                    <i class="bi bi-cloud-arrow-up text-success fs-3"></i>
                                    <p class="small mb-0 text-muted">Drag & Drop atau Klik di sini</p>
                                </div>
                                <input type="file" class="d-none file-input" data-category="2" multiple>
                            </div>
                        </div>
                    </div>

                    {{-- Kategori 3: Dokumentasi --}}
                    <div class="col-md-6">
                        <div class="card h-100 border-info shadow-sm upload-zone position-relative" data-category="3" style="cursor: pointer; transition: all 0.2s;">
                            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-camera me-2"></i>Dokumentasi</span>
                                <span class="badge bg-white text-info rounded-pill" id="count-dokumentasi">0</span>
                            </div>
                            <div class="card-body p-2 d-flex flex-column">
                                <ul class="list-group list-group-flush mb-2 flex-grow-1" id="list-dokumentasi" style="min-height: 50px;">
                                    <li class="list-group-item text-center text-muted small fst-italic py-3">Belum ada file.</li>
                                </ul>
                                <div class="p-3 text-center border rounded bg-light dashed-border mt-auto">
                                    <i class="bi bi-cloud-arrow-up text-info fs-3"></i>
                                    <p class="small mb-0 text-muted">Drag & Drop atau Klik di sini</p>
                                </div>
                                <input type="file" class="d-none file-input" data-category="3" multiple>
                            </div>
                        </div>
                    </div>

                    {{-- Kategori 4: Lainnya --}}
                    <div class="col-md-6">
                        <div class="card h-100 border-secondary shadow-sm upload-zone position-relative" data-category="4" style="cursor: pointer; transition: all 0.2s;">
                            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-paperclip me-2"></i>Lainnya</span>
                                <span class="badge bg-white text-secondary rounded-pill" id="count-lainnya">0</span>
                            </div>
                            <div class="card-body p-2 d-flex flex-column">
                                <ul class="list-group list-group-flush mb-2 flex-grow-1" id="list-lainnya" style="min-height: 50px;">
                                    <li class="list-group-item text-center text-muted small fst-italic py-3">Belum ada file.</li>
                                </ul>
                                <div class="p-3 text-center border rounded bg-light dashed-border mt-auto">
                                    <i class="bi bi-cloud-arrow-up text-secondary fs-3"></i>
                                    <p class="small mb-0 text-muted">Drag & Drop atau Klik di sini</p>
                                </div>
                                <input type="file" class="d-none file-input" data-category="4" multiple>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <div class="w-100">
                    <div id="upload-global-progress" class="progress mb-2" style="height: 5px; display: none;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div id="upload-global-error" class="alert alert-danger py-1 small mb-0" style="display: none;"></div>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


{{-- Finish Meeting Confirmation Modal --}}
<div class="modal fade" id="finishMeetingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Selesaikan Rapat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Apakah Anda yakin ingin menyelesaikan rapat berikut?</p>
                <h6 class="text-primary mt-3 mb-3" id="finish-meeting-title"></h6>
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Setelah rapat diselesaikan:
                    <ul class="mb-0 mt-2">
                        <li>Status akan berubah menjadi "Selesai"</li>
                        <li>Ruangan akan tersedia kembali</li>
                        <li>QR Code tidak lagi dapat diakses</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="finish-meeting-form" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-2"></i>Ya, Selesaikan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const highlightId = "{{ session('highlight_id') }}";
        if (highlightId) {
            const row = document.getElementById('rapat-row-' + highlightId);
            if (row) {
                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
</script>
@endpush
@endsection

@push('styles')
<style>
    .status-indicator {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 8px;
    }
    .status-available { background-color: #28a745; }
    .status-unavailable { background-color: #dc3545; }
    select option:disabled {
        color: #adb5bd;
        background-color: #e9ecef;
    }
    .modal-backdrop.show {
        opacity: 0.7; 
        -webkit-backdrop-filter: blur(5px);
        backdrop-filter: blur(5px);
    }
</style>
@endpush

@push('scripts')
<script>
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  })

  document.addEventListener('DOMContentLoaded', function () {
    const allRapats = @json($allRapats);
    const addModalEl = document.getElementById('addRapatModal');

    // --- FUNGSI UNTUK AUTO-REFRESH RUANGAN BERDASARKAN CABANG ---
    async function updateRoomOptions(cabangSelect, roomSelect) {
        console.log('updateRoomOptions triggered');
        const cabangId = cabangSelect.value;
        
        roomSelect.innerHTML = '<option value="">-- Pilih Ruangan --</option>';
        roomSelect.disabled = true;
        
        const modal = document.getElementById('addRapatModal'); // Explicitly get modal
        const helpText = modal.querySelector('.room-help-text');
        if (helpText) {
            helpText.textContent = 'Memuat ruangan...';
            helpText.style.display = 'block';
            helpText.className = 'form-text text-muted room-help-text'; // Reset class
        }
        
        if (!cabangId) {
            if (helpText) helpText.textContent = 'Pilih cabang terlebih dahulu.';
            return;
        }

        try {
            const url = "{{ route('cabang.rooms', ':id') }}".replace(':id', cabangId);
            const response = await fetch(url);
            if (!response.ok) throw new Error('Gagal mengambil data ruangan');
            
            const rooms = await response.json();

            if (rooms.length === 0) {
                if (helpText) helpText.textContent = 'Tidak ada ruangan tersedia untuk cabang ini.';
                return;
            }

            rooms.forEach(room => {
                const option = new Option(room.room, room.id_room);
                option.dataset.statusId = room.status_ruangan ? room.status_ruangan.id_status : room.status_ruangan_id;
                option.dataset.roomName = room.room;
                roomSelect.add(option);
            });

            const dateInput = modal.querySelector('.date-input');
            const startTimeInput = modal.querySelector('.time-input[name="waktu_start"]');
            
            if (dateInput.value && startTimeInput.value) {
                roomSelect.disabled = false;
                if (helpText) helpText.style.display = 'none';
            } else {
                roomSelect.disabled = false;
                if (helpText) {
                    helpText.textContent = 'Isi tanggal & waktu mulai untuk melihat ketersediaan ruangan.';
                    helpText.classList.remove('text-danger');
                    helpText.classList.add('text-warning');
                    helpText.style.display = 'block';
                }
            }

        } catch (error) {
            console.error('Error fetching rooms:', error);
            if (helpText) helpText.textContent = 'Gagal memuat ruangan. Silakan coba lagi.';
            alert('Terjadi kesalahan saat memuat data ruangan.');
        }
    }

    function checkRoomAvailability(modal) {
        const dateInput = modal.querySelector('.date-input');
        const startTimeInput = modal.querySelector('.time-input[name="waktu_start"]');
        const endTimeInput = modal.querySelector('.time-input[name="waktu_end"]');
        const roomSelect = modal.querySelector('.room-select');
        const roomHelpText = modal.querySelector('.room-help-text');

        clearError(roomSelect);

        const selectedDate = dateInput.value;
        const selectedStartTime = startTimeInput.value;
        const selectedEndTime = endTimeInput.value;
        const hasRoomOptions = roomSelect.options.length > 1;

        if (!selectedDate || !selectedStartTime) {
            if (!hasRoomOptions) {
                roomSelect.disabled = true;
                roomHelpText.style.display = 'block';
            } else {
                roomHelpText.textContent = 'Pilih tanggal & waktu mulai untuk melihat ketersediaan ruangan.';
                roomHelpText.classList.remove('text-danger');
                roomHelpText.classList.add('text-warning');
                roomHelpText.style.display = 'block';
            }
            return;
        } else {
            if (hasRoomOptions) {
                roomSelect.disabled = false;
                roomHelpText.style.display = 'none';
            }
        }

        const selectedStartDateTime = new Date(`${selectedDate}T${selectedStartTime}`);
        const selectedEndDateTime = selectedEndTime ? new Date(`${selectedDate}T${selectedEndTime}`) : new Date(selectedStartDateTime.getTime() + 60 * 60 * 1000);

        Array.from(roomSelect.options).forEach(option => {
            if (!option.value) return;

            const roomId = option.value;
            const staticStatusId = option.getAttribute('data-status-id');

            let isAvailable = true;
            let reason = '';

            if (staticStatusId == 2) {
                isAvailable = false;
                reason = ' (Status: Tidak Tersedia)';
            }

            if (isAvailable) {
                for (const rapat of allRapats) {
                    if (rapat.id_room == roomId && rapat.tanggal === selectedDate) {
                        const rapatStart = new Date(`${rapat.tanggal}T${rapat.waktu_start}`);
                        const rapatEnd = rapat.waktu_end ? new Date(`${rapat.tanggal}T${rapat.waktu_end}`) : new Date(rapatStart.getTime() + 60 * 60 * 1000);
                        
                        if (selectedStartDateTime < rapatEnd && selectedEndDateTime > rapatStart) {
                            isAvailable = false;
                            reason = ` (Dipakai pkl ${rapat.waktu_start.substring(0,5)}${rapat.waktu_end ? ' - ' + rapat.waktu_end.substring(0,5) : ''})`;
                            break;
                        }
                    }
                }
            }

            option.disabled = !isAvailable;
            const statusClass = isAvailable ? 'status-available' : 'status-unavailable';
            const roomName = option.dataset.roomName;
            option.innerHTML = `<span class="status-indicator ${statusClass}"></span> ${roomName}${reason}`;
        });
    }

    const showError = (input, message) => {
        input.classList.add('is-invalid');
        const error = input.parentElement.querySelector('.invalid-feedback');
        if (error) error.textContent = message;
    };

    const clearError = (input) => {
        input.classList.remove('is-invalid');
    };

    const validateForm = (form) => {
        let isValid = true;
        form.querySelectorAll('.is-invalid').forEach(clearError);

        const inputs = form.querySelectorAll('[required]');
        inputs.forEach(input => {
            if (!input.value.trim() && !input.disabled) {
                showError(input, `Kolom ${input.previousElementSibling.textContent} tidak boleh kosong.`);
                isValid = false;
            }
        });

        const dateInput = form.querySelector('.date-input');
        const startTimeInput = form.querySelector('.time-input[name="waktu_start"]');
        const endTimeInput = form.querySelector('.time-input[name="waktu_end"]');

        if (dateInput.value && startTimeInput.value) {
            const selectedDateTime = new Date(`${dateInput.value}T${startTimeInput.value}`);
            const now = new Date();
            now.setMinutes(now.getMinutes() - 1);

            if (selectedDateTime < now) {
                showError(startTimeInput, 'Waktu mulai tidak boleh di masa lampau.');
                isValid = false;
            }
        }

        if (startTimeInput.value && endTimeInput.value && endTimeInput.value <= startTimeInput.value) {
            showError(endTimeInput, 'Waktu selesai harus setelah waktu mulai.');
            isValid = false;
        }

        return isValid;
    };

    document.getElementById('addRapatForm').addEventListener('submit', function(e) {
        if (!validateForm(this)) e.preventDefault();
    });

    document.getElementById('add_id_cabang').addEventListener('change', function() {
        const roomSelect = document.getElementById('add_id_room');
        updateRoomOptions(this, roomSelect).then(() => {
            checkRoomAvailability(addModalEl);
        });
    });

    ['add_tanggal', 'add_waktu_start', 'add_waktu_end'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', () => checkRoomAvailability(addModalEl));
            el.addEventListener('input', () => checkRoomAvailability(addModalEl)); // Add input event
        }
    });

    addModalEl.addEventListener('show.bs.modal', function() {
        const form = addModalEl.querySelector('form');
        form.reset();
        
        const roomSelect = addModalEl.querySelector('.room-select');
        roomSelect.innerHTML = '<option value="">-- Pilih Ruangan --</option>';
        roomSelect.disabled = true;
        
        addModalEl.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        addModalEl.querySelector('.room-help-text').style.display = 'block';

        document.getElementById('add_files').value = '';
        document.getElementById('add-files-list').innerHTML = '';
    });

    const addFileInput = document.getElementById('add_files');
    const addFileList = document.getElementById('add-files-list');
    let addFileDataTransfer = new DataTransfer();

    function getFileIcon(fileType) {
        if (fileType.includes('pdf')) return '<i class="bi bi-file-earmark-pdf text-danger me-2"></i>';
        if (fileType.includes('word')) return '<i class="bi bi-file-earmark-word text-primary me-2"></i>';
        if (fileType.includes('presentation') || fileType.includes('powerpoint')) return '<i class="bi bi-file-earmark-ppt text-warning me-2"></i>';
        if (fileType.includes('image')) return '<i class="bi bi-file-earmark-image text-info me-2"></i>';
        return '<i class="bi bi-file-earmark-text text-secondary me-2"></i>';
    }

    function renderFileList(fileListElement, dataTransfer) {
        fileListElement.innerHTML = '';
        if (dataTransfer.files.length === 0) return;

        Array.from(dataTransfer.files).forEach((file, index) => {
            const li = document.createElement('li');
            li.className = 'list-group-item list-group-item-light d-flex justify-content-between align-items-center';

            const fileInfo = document.createElement('span');
            fileInfo.innerHTML = `${getFileIcon(file.type)} ${file.name}`;

            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'btn btn-outline-danger btn-sm';
            deleteBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
            deleteBtn.onclick = () => {
                const newFiles = new DataTransfer();
                Array.from(dataTransfer.files).forEach((f, i) => {
                    if (i !== index) {
                        newFiles.items.add(f);
                    }
                });
                
                addFileDataTransfer = newFiles;
                addFileInput.files = newFiles.files;
                renderFileList(addFileList, addFileDataTransfer);
            };

            li.appendChild(fileInfo);
            li.appendChild(deleteBtn);
            fileListElement.appendChild(li);
        });
    }

    addFileInput.addEventListener('change', function() {
        const maxFileSize = 5 * 1024 * 1024;
        const errorElement = document.getElementById('add-files-error');

        errorElement.textContent = '';
        errorElement.style.display = 'none';
        this.classList.remove('is-invalid');
        const oversizedFiles = [];
        const allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt'];
        const invalidTypeFiles = [];

        Array.from(this.files).forEach(file => {
            const fileExtension = file.name.split('.').pop().toLowerCase();

            if (file.size > maxFileSize) {
                oversizedFiles.push(file.name);
            } else if (!allowedExtensions.includes(fileExtension)) {
                invalidTypeFiles.push(file.name);
            } else {
                addFileDataTransfer.items.add(file);
            }
        });
        this.files = addFileDataTransfer.files;
        renderFileList(addFileList, addFileDataTransfer);

        let errorMessage = '';
        if (oversizedFiles.length > 0) {
            errorMessage += `File terlalu besar (>5MB): ${oversizedFiles.join(', ')}. `;
        }
        if (invalidTypeFiles.length > 0) {
            errorMessage += `Tipe file tidak didukung: ${invalidTypeFiles.join(', ')}. `;
        }

        if (errorMessage) {
            this.classList.add('is-invalid');
            errorElement.textContent = errorMessage;
            errorElement.style.display = 'block';
        }
    });

    addModalEl.addEventListener('show.bs.modal', function() {
        addFileDataTransfer = new DataTransfer();
        addFileInput.value = '';
        renderFileList(addFileList, addFileDataTransfer);
    });

    document.querySelectorAll('#addRapatModal input, #addRapatModal select').forEach(input => {
        const eventType = input.tagName === 'SELECT' ? 'change' : 'input';
        input.addEventListener(eventType, (e) => {
            clearError(e.target);
        });
    });

    const searchInput = document.getElementById('search-input');
    const filterForm = document.getElementById('filter-form');
    let debounceTimer;

    searchInput.addEventListener('input', function (e) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            if (filterForm) {
                const formData = new FormData(filterForm);
                const params = new URLSearchParams();
                for (const pair of formData.entries()) {
                    if (pair[1]) params.append(pair[0], pair[1]);
                }
                window.location.href = `{{ route('pic.meetings.index') }}?${params.toString()}`;
            }
        }, 500);
    });

    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        document.getElementById('addRapatForm').addEventListener('submit', function(e) {
            if (validateForm(this)) {
                loadingOverlay.style.display = 'flex';
            }
        });

        document.querySelectorAll('.delete-meeting-form').forEach(form => {
            form.addEventListener('submit', function() {
                loadingOverlay.style.display = 'flex';
            });
        });
    }

    // --- DETAIL RAPAT MODAL LOGIC ---
    const detailModalEl = document.getElementById('detailRapatModal');
    if (detailModalEl) {
        detailModalEl.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const rapatId = button.getAttribute('data-id');
            const modalBody = this.querySelector('.modal-body');
            const loading = document.getElementById('detail-loading');
            const content = document.getElementById('detail-content');

            // Reset state
            loading.style.display = 'block';
            content.style.display = 'none';

            // Fetch data
            const url = "{{ route('pic.meetings.show', ':id') }}".replace(':id', rapatId);
            console.log('Fetching meeting details from:', url);
            
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Received data:', data);
                
                if (data.error) {
                    throw new Error(data.error);
                }
                
                document.getElementById('detail-judul').textContent = data.judul;
                
                const statusBadge = document.getElementById('detail-status');
                statusBadge.textContent = data.status;
                statusBadge.className = `badge ${data.status_class}`;

                // Handle Rejection Note
                const rejectionRow = document.getElementById('row-rejection-note');
                const rejectionNote = document.getElementById('detail-rejection-note');
                
                if (data.status === 'Ditolak' && data.rejection_note) {
                    rejectionNote.textContent = data.rejection_note;
                    rejectionRow.style.display = 'table-row';
                } else {
                    rejectionRow.style.display = 'none';
                }

                document.getElementById('detail-cabang').textContent = data.cabang;
                document.getElementById('detail-room').textContent = data.room;
                document.getElementById('detail-tanggal').textContent = data.tanggal_formatted;
                document.getElementById('detail-waktu').textContent = data.waktu;
                document.getElementById('detail-desc').textContent = data.deskripsi || '-';
                document.getElementById('detail-pengaju').textContent = data.pengaju;

                // Populate Files
                const fileList = document.getElementById('detail-files-list');
                fileList.innerHTML = '';
                if (data.files && data.files.length > 0) {
                    data.files.forEach(file => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item d-flex justify-content-between align-items-center';
                        li.innerHTML = `
                            <a href="${file.download_url}" target="_blank" class="text-decoration-none text-truncate" style="max-width: 80%;">
                                <i class="bi bi-file-earmark me-2"></i>${file.file_name}
                            </a>
                        `;
                        fileList.appendChild(li);
                    });
                } else {
                    fileList.innerHTML = '<li class="list-group-item text-muted">Tidak ada file lampiran.</li>';
                }

                // Update Links
                document.getElementById('btn-lihat-absensi').href = "{{ route('pic.meetings.absensi', ':id') }}".replace(':id', rapatId);
                
                const btnQr = document.getElementById('btn-lihat-qr');
                if (data.status === 'Berlangsung') {
                    btnQr.style.display = 'inline-block';
                    btnQr.href = "{{ route('pic.meetings.qr', ':id') }}".replace(':id', rapatId);
                } else {
                    btnQr.style.display = 'none';
                }

                // Show content
                loading.style.display = 'none';
                content.style.display = 'block';

            })
            .catch(error => {
                console.error('Error:', error);
                loading.innerHTML = `<div class="text-danger">Terjadi kesalahan: ${error.message}</div>`;
            });
        });
    }

    // --- UPLOAD FILE MODAL LOGIC ---
    const uploadFileModalEl = document.getElementById('uploadFileModal');
    if (uploadFileModalEl) {
        let currentRapatId = null;

        uploadFileModalEl.addEventListener('show.bs.modal', function (event) {
            try {
                const button = event.relatedTarget;
                if (!button) {
                    console.warn('Upload modal triggered without relatedTarget');
                    return;
                }
                currentRapatId = button.getAttribute('data-id');
                console.log('Opening upload modal for meeting ID:', currentRapatId);

                const modalTitle = document.getElementById('upload-modal-judul');
                
                // Reset UI
                modalTitle.textContent = 'Loading...';
                resetUploadModal();

                // Fetch Meeting Data
                const url = "{{ route('pic.meetings.show', ':id') }}".replace(':id', currentRapatId);
                
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(res => {
                        if (!res.ok) throw new Error('Network response was not ok');
                        return res.json();
                    })
                    .then(data => {
                        if (data.error) throw new Error(data.error);
                        
                        modalTitle.textContent = data.judul;
                        renderAllCategories(data.files);
                    })
                    .catch(err => {
                        console.error('Error fetching meeting details:', err);
                        modalTitle.textContent = 'Error';
                        alert('Gagal memuat data: ' + err.message);
                    });
            } catch (e) {
                console.error('Error in show.bs.modal handler:', e);
            }
        });

        function resetUploadModal() {
            ['materi', 'notulensi', 'dokumentasi', 'lainnya'].forEach(cat => {
                document.getElementById(`list-${cat}`).innerHTML = '<li class="list-group-item text-center text-muted small fst-italic py-3">Memuat...</li>';
                document.getElementById(`count-${cat}`).textContent = '0';
            });
            document.getElementById('upload-global-error').style.display = 'none';
            document.getElementById('upload-global-progress').style.display = 'none';
        }

        function renderAllCategories(files) {
            const categories = {
                1: { id: 'materi', list: [], el: document.getElementById('list-materi'), countEl: document.getElementById('count-materi') },
                2: { id: 'notulensi', list: [], el: document.getElementById('list-notulensi'), countEl: document.getElementById('count-notulensi') },
                3: { id: 'dokumentasi', list: [], el: document.getElementById('list-dokumentasi'), countEl: document.getElementById('count-dokumentasi') },
                4: { id: 'lainnya', list: [], el: document.getElementById('list-lainnya'), countEl: document.getElementById('count-lainnya') }
            };

            // Group files
            if (files) {
                files.forEach(file => {
                    if (categories[file.id_categories]) {
                        categories[file.id_categories].list.push(file);
                    } else {
                        // Fallback to 'lainnya' if category unknown
                        categories[4].list.push(file);
                    }
                });
            }

            // Render each category
            Object.values(categories).forEach(cat => {
                cat.el.innerHTML = '';
                cat.countEl.textContent = cat.list.length;

                if (cat.list.length > 0) {
                    cat.list.forEach(file => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item d-flex justify-content-between align-items-center px-2 py-1';
                        li.innerHTML = `
                            <a href="${file.download_url}" target="_blank" class="text-decoration-none text-truncate small" style="max-width: 85%;" title="${file.file_name}">
                                <i class="bi bi-file-earmark me-1"></i>${file.file_name}
                            </a>
                            <button class="btn btn-link text-danger p-0 btn-delete-file" data-id="${file.id_file}" title="Hapus">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        `;
                        cat.el.appendChild(li);
                    });
                } else {
                    cat.el.innerHTML = '<li class="list-group-item text-center text-muted small fst-italic py-3">Belum ada file.</li>';
                }
            });

            // Attach delete listeners
            document.querySelectorAll('.btn-delete-file').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (confirm('Hapus file ini?')) {
                        deleteFile(this.getAttribute('data-id'));
                    }
                });
            });
        }

        function deleteFile(fileId) {
            const url = "{{ route('pic.meetings.destroyFile', ':id') }}".replace(':id', fileId);
            
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    refreshFileList();
                } else {
                    alert(data.message || 'Gagal menghapus file');
                }
            })
            .catch(err => alert('Terjadi kesalahan koneksi.'));
        }

        function refreshFileList() {
            const url = "{{ route('pic.meetings.show', ':id') }}".replace(':id', currentRapatId);
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.json())
                .then(data => renderAllCategories(data.files));
        }

        // --- DRAG AND DROP & UPLOAD LOGIC ---
        const uploadZones = document.querySelectorAll('.upload-zone');

        uploadZones.forEach(zone => {
            const input = zone.querySelector('input[type="file"]');
            const categoryId = zone.getAttribute('data-category');

            // Click to upload (delegated)
            zone.addEventListener('click', (e) => {
                // Ignore clicks on links or delete buttons
                if (e.target.closest('a') || e.target.closest('.btn-delete-file')) {
                    return;
                }
                input.click();
            });

            // Input change
            input.addEventListener('change', (e) => {
                if (input.files.length > 0) {
                    handleFiles(input.files, categoryId);
                }
            });

            // Drag events
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('border-primary', 'bg-light'); // Highlight
                zone.style.transform = 'scale(1.02)'; // Slight zoom
                zone.style.boxShadow = '0 .5rem 1rem rgba(0,0,0,.15)'; // Stronger shadow
            });

            zone.addEventListener('dragleave', (e) => {
                e.preventDefault();
                zone.classList.remove('border-primary', 'bg-light');
                zone.style.transform = 'scale(1)';
                zone.style.boxShadow = '';
            });

            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('border-primary', 'bg-light');
                zone.style.transform = 'scale(1)';
                zone.style.boxShadow = '';
                
                if (e.dataTransfer.files.length > 0) {
                    handleFiles(e.dataTransfer.files, categoryId);
                }
            });
        });

        async function handleFiles(files, categoryId) {
            const errorDiv = document.getElementById('upload-global-error');
            const progressBar = document.getElementById('upload-global-progress');
            const progressBarInner = progressBar.querySelector('.progress-bar');
            
            errorDiv.style.display = 'none';
            errorDiv.textContent = ''; // Clear previous errors
            progressBar.style.display = 'flex';
            progressBarInner.style.width = '0%';
            progressBarInner.textContent = '';

            const validFiles = [];
            const maxFileSize = 20 * 1024 * 1024; // 20MB

            Array.from(files).forEach(file => {
                if (file.size > maxFileSize) {
                    const msg = `File ${file.name} terlalu besar (>20MB). `;
                    errorDiv.textContent += msg;
                    errorDiv.style.display = 'block';
                } else {
                    validFiles.push(file);
                }
            });

            if (validFiles.length === 0) {
                progressBar.style.display = 'none';
                return;
            }

            let successCount = 0;
            for (let i = 0; i < validFiles.length; i++) {
                const file = validFiles[i];
                progressBarInner.textContent = `Mengupload ${i + 1}/${validFiles.length}: ${file.name}`;
                
                // Initial progress for this file
                const startPercent = (i / validFiles.length) * 100;
                progressBarInner.style.width = `${startPercent}%`;

                try {
                    await uploadSingleFile(file, categoryId, (percent) => {
                        // Calculate overall progress: completed files + current file progress
                        const overallPercent = ((i + (percent / 100)) / validFiles.length) * 100;
                        progressBarInner.style.width = `${overallPercent}%`;
                    });
                    successCount++;
                } catch (error) {
                    console.error(error);
                    errorDiv.textContent += `Gagal upload ${file.name}: ${error.message}. `;
                    errorDiv.style.display = 'block';
                }
            }

            progressBar.style.display = 'none';
            progressBarInner.textContent = '';
            
            if (successCount > 0) {
                refreshFileList();
            }
        }

        function uploadSingleFile(file, categoryId, onProgress) {
            return new Promise((resolve, reject) => {
                const formData = new FormData();
                formData.append('id_rapat', currentRapatId);
                formData.append('id_categories', categoryId);
                formData.append('file', file);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', "{{ route('pic.meetings.storeFile', ':id') }}".replace(':id', currentRapatId), true);
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable && onProgress) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        onProgress(percentComplete);
                    }
                };

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                resolve(response);
                            } else {
                                reject(new Error(response.message || 'Upload gagal.'));
                            }
                        } catch (e) {
                            reject(new Error('Invalid response format.'));
                        }
                    } else {
                        reject(new Error(`HTTP Error ${xhr.status}`));
                    }
                };

                xhr.onerror = function() {
                    reject(new Error('Terjadi kesalahan koneksi.'));
                };

                xhr.send(formData);
            });
        }
    }


    // --- FINISH MEETING MODAL LOGIC ---
    const finishModalEl = document.getElementById('finishMeetingModal');
    const finishForm = document.getElementById('finish-meeting-form');
    const finishTitle = document.getElementById('finish-meeting-title');

    if (finishModalEl && finishForm && finishTitle) {
        document.querySelectorAll('.btn-finish-meeting').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Finish button clicked');
                
                const rapatId = this.getAttribute('data-rapat-id');
                const rapatJudul = this.getAttribute('data-rapat-judul');
                
                console.log('Rapat ID:', rapatId);
                console.log('Rapat Judul:', rapatJudul);
                
                // Update modal content
                finishTitle.textContent = rapatJudul;
                
                // Build action URL
                const baseUrl = '{{ route("pic.meetings.finish", ":id") }}';
                finishForm.action = baseUrl.replace(':id', rapatId);
                
                // Show modal using Bootstrap 5 method
                const modal = new bootstrap.Modal(finishModalEl);
                modal.show();
            });
        });
    } else {
        console.error('Finish meeting modal elements not found');
    }

  }); // End of DOMContentLoaded
</script>
@endpush
