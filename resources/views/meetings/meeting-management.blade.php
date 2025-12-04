@extends('layouts.app')

@section('content')
{{-- Elemen untuk Loading Overlay --}}
<div id="loading-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1060; justify-content: center; align-items: center;">
    <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<div class="container mt-4">

    {{-- ALERT LAMA DIGANTI SWEETALERT --}}
    {{-- @if (session('success'))
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
    @endif --}}

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Manajemen Rapat</h5>
            {{-- PERBAIKAN: Tooltip untuk tombol disabled --}}
            {{-- Tombol Tambah Rapat Selalu Aktif --}}
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addRapatModal">
                <i class="bi bi-plus-circle me-1"></i> Tambah Rapat
            </button>
        </div>
        <div class="card-body">
            <form action="{{ route('meetings.index') }}" method="GET" id="filter-form">
                <div class="row mb-3 g-2">
                    <div class="col-md-5">
                        <label for="picSelector" class="form-label visually-hidden">Pilih PIC</label>
                        <select name="id_user_pic" id="picSelector" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Semua PIC --</option>
                            @foreach ($pics as $pic)
                                <option value="{{ $pic->id_user }}" {{ request('id_user_pic') == $pic->id_user ? 'selected' : '' }}>
                                    {{ $pic->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-7">
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
                                {{-- Link untuk sorting berdasarkan judul --}}
                                <a href="{{ route('meetings.index', array_merge(request()->query(), ['sort' => 'judul', 'direction' => ($sort === 'judul' && $direction === 'asc') ? 'desc' : 'asc'])) }}" class="text-decoration-none text-black">
                                    Judul
                                    @if ($sort === 'judul')
                                        <i class="bi {{ $direction === 'asc' ? 'bi-sort-alpha-down' : 'bi-sort-alpha-up' }}"></i>
                                    @else
                                        {{-- Ikon default jika kolom lain yang diurutkan --}}
                                        <i class="bi bi-sort-alpha-down"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Cabang</th>
                            <th>Ruangan</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="meetingsTableBody">
                        @forelse ($rapats as $rapat)
                            <tr data-rapat-id="{{ $rapat->id_rapat }}">
                                {{-- PERBAIKAN: Penomoran yang benar untuk paginasi --}}
                                @if ($rapats instanceof \Illuminate\Pagination\AbstractPaginator)
                                    <td>{{ $rapats->firstItem() + $loop->index }}</td>
                                @else
                                    <td>{{ $loop->iteration }}</td>
                                @endif
                                <td>{{ $rapat->judul }}</td>
                                <td>{{ $rapat->cabang ? $rapat->cabang->cabang : 'N/A' }}</td>
                                <td>{{ $rapat->room ? $rapat->room->room : 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($rapat->tanggal)->translatedFormat(    'd/m/Y') }}</td>
                                <td>{{ substr($rapat->waktu_start, 0, 5) }} - {{ $rapat->waktu_end ? substr($rapat->waktu_end, 0, 5) : 'Selesai tidak menentu' }}</td>
                                <td>
                                    @php
                                        $statusText = $rapat->status ? $rapat->status->status_rapat : 'N/A';
                                        $statusClass = 'bg-secondary'; // Warna default
                                        switch (strtolower($statusText)) {
                                            case 'diterima':
                                                $statusClass = 'bg-success';
                                                break;
                                            case 'ditolak':
                                                $statusClass = 'bg-danger';
                                                break;
                                            case 'menunggu':
                                                $statusClass = 'bg-warning text-dark';
                                                break;
                                            case 'berlangsung':
                                                $statusClass = 'bg-primary';
                                                break;
                                            case 'selesai':
                                                $statusClass = 'bg-dark';
                                                break;
                                        }
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ $statusText }}</span></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton{{ $rapat->id_rapat }}" data-bs-toggle="dropdown" aria-expanded="false" data-bs-boundary="viewport" data-bs-popper-config='{"strategy":"fixed"}'>Aksi</button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $rapat->id_rapat }}">
                                            <li><a class="dropdown-item edit-btn" href="#" data-bs-toggle="modal" data-bs-target="#editRapatModal" data-rapat="{{ json_encode($rapat) }}"><i class="bi bi-pencil-square me-2"></i>Edit</a></li>
                                            <li><a class="dropdown-item" href="{{ route('meetings.showAbsensi', $rapat->id_rapat) }}" target="_blank"><i class="bi bi-person-check me-2"></i>Absensi</a></li>
                                            {{-- PERUBAHAN: Link QR Code diubah untuk memicu modal --}}
                                            @php
                                                $statusTextForQr = $rapat->status ? $rapat->status->status_rapat : 'N/A';
                                            @endphp
                                            <li>
                                                <a class="dropdown-item qr-code-btn" href="#" data-qr-url="{{ route('meetings.showQr', $rapat->id_rapat) }}" data-rapat-status="{{ $statusTextForQr }}">
                                                    <i class="bi bi-qr-code me-2"></i>QR Code
                                                </a>
                                            </li>
                                            {{-- PERUBAHAN: Link Guest Mode diubah untuk menampilkan halaman QR --}}
                                            <li><a class="dropdown-item" href="{{ route('meetings.showGuestQr', $rapat->id_rapat) }}" target="_blank"><i class="bi bi-person-badge me-2"></i>Guest Mode</a></li>
                                            <li>
                                                <form action="{{ route('meetings.destroy', $rapat->id_rapat) }}" method="POST" class="d-inline delete-meeting-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Anda yakin ingin menghapus rapat ini?')"><i class="bi bi-trash me-2"></i>Hapus</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    Tidak ada data rapat yang cocok dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Tampilkan Paginasi jika data adalah instance Paginator --}}
            @if ($rapats instanceof \Illuminate\Pagination\AbstractPaginator)
                <div class="d-flex justify-content-end mt-3">
                    {{-- appends(request()->query()) memastikan filter dan sort tetap ada saat pindah halaman --}}
                    {{ $rapats->appends(request()->query())->links('pagination::simple-bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Add Rapat Modal --}}
<div class="modal fade" id="addRapatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('meetings.store') }}" method="POST" class="modal-content" id="addRapatForm" novalidate enctype="multipart/form-data">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah Rapat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="addRapatBody">
                {{-- URUTAN FIELD DIPERBAIKI --}}
                {{-- TAMBAHAN: Hidden input untuk menangkap PIC dari filter --}}
                <input type="hidden" name="id_user_pic_from_filter" value="{{ request('id_user_pic') }}">
                <div class="mb-3">
                    <label for="add_judul" class="form-label">Judul Rapat <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="add_judul" name="judul" required>
                    <div class="invalid-feedback">Judul rapat tidak boleh kosong.</div>
                </div>
                <div class="mb-3">
                    <label for="add_id_user_pengaju" class="form-label">PIC <span class="text-danger">*</span></label>
                    <select class="form-select" id="add_id_user_pengaju" name="id_user_pengaju" required> {{-- ID disesuaikan --}}
                        <option value="">-- Pilih PIC --</option>
                        @foreach($pics as $pic)
                            <option value="{{ $pic->id_user }}" {{ request('id_user_pic') == $pic->id_user ? 'selected' : '' }}>{{ $pic->name }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">Silakan pilih PIC.</div>
                </div>
                <div class="mb-3">
                    <label for="add_tanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
                    {{-- Mengembalikan ke input date asli --}}
                    <input type="date" class="form-control date-input" id="add_tanggal" name="tanggal" required>
                    <div class="invalid-feedback">Tanggal tidak boleh kosong.</div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="add_waktu_start" class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                        {{-- Mengembalikan ke input time asli --}}
                        <input type="time" class="form-control time-input" id="add_waktu_start" name="waktu_start" required>
                        <div class="invalid-feedback">Waktu mulai tidak boleh kosong atau di masa lampau.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="add_waktu_end" class="form-label">Waktu Selesai</label>
                        {{-- Mengembalikan ke input time asli --}}
                        <input type="time" class="form-control time-input" id="add_waktu_end" name="waktu_end">
                        <div class="invalid-feedback">Waktu selesai harus setelah waktu mulai.</div>
                    </div>
                </div>
                <div class="row">
                              <div class="col-md-6 mb-3">
                        <label for="add_id_cabang" class="form-label">Cabang <span class="text-danger">*</span></label>
                        <select class="form-select" id="add_id_cabang" name="id_cabang" required>
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($cabangs as $cabang)
                                <option value="{{ $cabang->id }}">{{ $cabang->cabang }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Silakan pilih cabang.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="add_id_room" class="form-label">Ruangan <span class="text-danger">*</span></label>
                        <select class="form-select room-select" id="add_id_room" name="id_room" required disabled>
                            <option value="">-- Pilih Ruangan --</option>
                            {{-- Opsi ruangan akan diisi oleh JavaScript --}}
                        </select>
                        <small class="form-text text-danger room-help-text">Pilih tanggal & waktu mulai dulu.</small>
                        <div class="invalid-feedback">Silakan pilih ruangan yang tersedia.</div>
                    </div>
                </div>
                {{-- TAMBAHAN: Field Deskripsi --}}
                <div class="mb-3">
                    <label for="add_desc" class="form-label">Deskripsi (Opsional)</label>
                    <textarea class="form-control" id="add_desc" name="desc" rows="2"></textarea>
                </div>
                {{-- TAMBAHAN: Field Upload Dokumen Berdasarkan Kategori --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Upload Dokumen</label>
                    
                    <div class="row g-3 mb-4">
                        {{-- Kategori 1: Materi --}}
                        <div class="col-md-6">
                            <div class="card h-100 border-primary shadow-sm upload-zone position-relative" data-target="add_files_materi" style="cursor: pointer; transition: all 0.2s;">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-file-earmark-text me-2"></i>Materi</span>
                                    <span class="badge bg-white text-primary rounded-pill" id="count-add_files_materi">0</span>
                                </div>
                                <div class="card-body p-2 d-flex flex-column">
                                    <ul class="list-group list-group-flush mb-2 flex-grow-1" id="list-add_files_materi" style="min-height: 50px;">
                                        <li class="list-group-item text-center text-muted small fst-italic py-3">Belum ada file.</li>
                                    </ul>
                                    <div class="p-3 text-center border rounded bg-light dashed-border mt-auto">
                                        <i class="bi bi-cloud-arrow-up text-primary fs-3"></i>
                                        <p class="small mb-0 text-muted">Drag & Drop atau Klik di sini</p>
                                    </div>
                                    <input type="file" class="d-none" id="add_files_materi" name="files_materi[]" multiple>
                                </div>
                            </div>
                        </div>

                        {{-- Kategori 2: Notulensi --}}
                        <div class="col-md-6">
                            <div class="card h-100 border-success shadow-sm upload-zone position-relative" data-target="add_files_notulensi" style="cursor: pointer; transition: all 0.2s;">
                                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-journal-text me-2"></i>Notulensi</span>
                                    <span class="badge bg-white text-success rounded-pill" id="count-add_files_notulensi">0</span>
                                </div>
                                <div class="card-body p-2 d-flex flex-column">
                                    <ul class="list-group list-group-flush mb-2 flex-grow-1" id="list-add_files_notulensi" style="min-height: 50px;">
                                        <li class="list-group-item text-center text-muted small fst-italic py-3">Belum ada file.</li>
                                    </ul>
                                    <div class="p-3 text-center border rounded bg-light dashed-border mt-auto">
                                        <i class="bi bi-cloud-arrow-up text-success fs-3"></i>
                                        <p class="small mb-0 text-muted">Drag & Drop atau Klik di sini</p>
                                    </div>
                                    <input type="file" class="d-none" id="add_files_notulensi" name="files_notulensi[]" multiple>
                                </div>
                            </div>
                        </div>

                        {{-- Kategori 3: Dokumentasi --}}
                        <div class="col-md-6">
                            <div class="card h-100 border-info shadow-sm upload-zone position-relative" data-target="add_files_dokumentasi" style="cursor: pointer; transition: all 0.2s;">
                                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-camera me-2"></i>Dokumentasi</span>
                                    <span class="badge bg-white text-info rounded-pill" id="count-add_files_dokumentasi">0</span>
                                </div>
                                <div class="card-body p-2 d-flex flex-column">
                                    <ul class="list-group list-group-flush mb-2 flex-grow-1" id="list-add_files_dokumentasi" style="min-height: 50px;">
                                        <li class="list-group-item text-center text-muted small fst-italic py-3">Belum ada file.</li>
                                    </ul>
                                    <div class="p-3 text-center border rounded bg-light dashed-border mt-auto">
                                        <i class="bi bi-cloud-arrow-up text-info fs-3"></i>
                                        <p class="small mb-0 text-muted">Drag & Drop atau Klik di sini</p>
                                    </div>
                                    <input type="file" class="d-none" id="add_files_dokumentasi" name="files_dokumentasi[]" multiple>
                                </div>
                            </div>
                        </div>

                        {{-- Kategori 4: Lainnya --}}
                        <div class="col-md-6">
                            <div class="card h-100 border-secondary shadow-sm upload-zone position-relative" data-target="add_files_lainnya" style="cursor: pointer; transition: all 0.2s;">
                                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-paperclip me-2"></i>Lainnya</span>
                                    <span class="badge bg-white text-secondary rounded-pill" id="count-add_files_lainnya">0</span>
                                </div>
                                <div class="card-body p-2 d-flex flex-column">
                                    <ul class="list-group list-group-flush mb-2 flex-grow-1" id="list-add_files_lainnya" style="min-height: 50px;">
                                        <li class="list-group-item text-center text-muted small fst-italic py-3">Belum ada file.</li>
                                    </ul>
                                    <div class="p-3 text-center border rounded bg-light dashed-border mt-auto">
                                        <i class="bi bi-cloud-arrow-up text-secondary fs-3"></i>
                                        <p class="small mb-0 text-muted">Drag & Drop atau Klik di sini</p>
                                    </div>
                                    <input type="file" class="d-none" id="add_files_lainnya" name="files_lainnya[]" multiple>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Elemen untuk menampilkan pesan error ukuran file --}}
                    <div id="add-files-error" class="invalid-feedback" style="display: none;"></div>
                    <small class="form-text text-muted mt-2 d-block">Bisa pilih lebih dari satu file (Ctrl+Klik). Tipe: jpg, png, pdf, doc, docx, ppt, pptx, txt. Maks 20MB/file.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Rapat Modal --}}
<div class="modal fade" id="editRapatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" class="modal-content" id="editRapatForm" novalidate enctype="multipart/form-data">
            @csrf 
            @method('PUT')
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Edit Rapat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_rapat" id="edit_id_rapat">
                <div class="mb-3">
                    <label for="edit_judul" class="form-label">Judul Rapat</label>
                    <input type="text" class="form-control" id="edit_judul" name="judul" required>
                    <div class="invalid-feedback">Judul rapat tidak boleh kosong.</div>
                </div>
                {{-- URUTAN FIELD DIPERBAIKI --}}
                <div class="mb-3">
                    <label for="edit_tanggal" class="form-label">Tanggal</label>
                    {{-- Mengembalikan ke input date asli --}}
                    <input type="date" class="form-control date-input" id="edit_tanggal" name="tanggal" required>
                    <div class="invalid-feedback">Tanggal tidak boleh kosong.</div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_waktu_start" class="form-label">Waktu Mulai</label>
                        {{-- Mengembalikan ke input time asli --}}
                        <input type="time" class="form-control time-input" id="edit_waktu_start" name="waktu_start" required>
                        <div class="invalid-feedback">Waktu mulai tidak boleh kosong atau di masa lampau.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_waktu_end" class="form-label">Waktu Selesai</label>
                        {{-- Mengembalikan ke input time asli --}}
                        <input type="time" class="form-control time-input" id="edit_waktu_end" name="waktu_end">
                        <div class="invalid-feedback">Waktu selesai harus setelah waktu mulai.</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_id_cabang" class="form-label">Cabang</label>
                        <select class="form-select" id="edit_id_cabang" name="id_cabang" required>
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($cabangs as $cabang)
                                <option value="{{ $cabang->id }}">{{ $cabang->cabang }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Silakan pilih cabang.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_id_room" class="form-label">Ruangan</label>
                        <select class="form-select room-select" id="edit_id_room" name="id_room" disabled required>
                            <option value="">-- Pilih Ruangan --</option>
                            {{-- Opsi ruangan akan diisi oleh JavaScript --}}
                        </select>
                        <small class="form-text text-danger room-help-text">Pilih tanggal & waktu mulai dulu.</small>
                        <div class="invalid-feedback">Silakan pilih ruangan yang tersedia.</div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="edit_id_status" class="form-label">Status Rapat</label>
                    <select class="form-select" id="edit_id_status" name="id_status" required>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id_status }}">{{ $status->status_rapat }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- TAMBAHAN: Field Deskripsi --}}
                <div class="mb-3">
                    <label for="edit_desc" class="form-label">Deskripsi (Opsional)</label>
                    <textarea class="form-control" id="edit_desc" name="desc" rows="2"></textarea>
                </div>
                {{-- TAMBAHAN: Field Upload Dokumen & Daftar File --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Dokumen Pendukung</label>
                    
                    <div class="row g-3 mb-4">
                        {{-- Kategori 1: Materi --}}
                        <div class="col-md-6">
                            <div class="card h-100 border-primary shadow-sm upload-zone position-relative" data-target="edit_files_materi" style="cursor: pointer; transition: all 0.2s;">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-file-earmark-text me-2"></i>Materi</span>
                                    <span class="badge bg-white text-primary rounded-pill" id="count-edit_files_materi">0</span>
                                </div>
                                <div class="card-body p-2 d-flex flex-column">
                                    <ul class="list-group list-group-flush mb-2 flex-grow-1" id="list-edit_files_materi" style="min-height: 50px;">
                                        <li class="list-group-item text-center text-muted small fst-italic py-3">Belum ada file.</li>
                                    </ul>
                                    <div class="p-3 text-center border rounded bg-light dashed-border mt-auto">
                                        <i class="bi bi-cloud-arrow-up text-primary fs-3"></i>
                                        <p class="small mb-0 text-muted">Drag & Drop atau Klik di sini</p>
                                    </div>
                                    <input type="file" class="d-none" id="edit_files_materi" name="files_materi[]" multiple>
                                </div>
                            </div>
                        </div>

                        {{-- Kategori 2: Notulensi --}}
                        <div class="col-md-6">
                            <div class="card h-100 border-success shadow-sm upload-zone position-relative" data-target="edit_files_notulensi" style="cursor: pointer; transition: all 0.2s;">
                                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-journal-text me-2"></i>Notulensi</span>
                                    <span class="badge bg-white text-success rounded-pill" id="count-edit_files_notulensi">0</span>
                                </div>
                                <div class="card-body p-2 d-flex flex-column">
                                    <ul class="list-group list-group-flush mb-2 flex-grow-1" id="list-edit_files_notulensi" style="min-height: 50px;">
                                        <li class="list-group-item text-center text-muted small fst-italic py-3">Belum ada file.</li>
                                    </ul>
                                    <div class="p-3 text-center border rounded bg-light dashed-border mt-auto">
                                        <i class="bi bi-cloud-arrow-up text-success fs-3"></i>
                                        <p class="small mb-0 text-muted">Drag & Drop atau Klik di sini</p>
                                    </div>
                                    <input type="file" class="d-none" id="edit_files_notulensi" name="files_notulensi[]" multiple>
                                </div>
                            </div>
                        </div>

                        {{-- Kategori 3: Dokumentasi --}}
                        <div class="col-md-6">
                            <div class="card h-100 border-info shadow-sm upload-zone position-relative" data-target="edit_files_dokumentasi" style="cursor: pointer; transition: all 0.2s;">
                                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-camera me-2"></i>Dokumentasi</span>
                                    <span class="badge bg-white text-info rounded-pill" id="count-edit_files_dokumentasi">0</span>
                                </div>
                                <div class="card-body p-2 d-flex flex-column">
                                    <ul class="list-group list-group-flush mb-2 flex-grow-1" id="list-edit_files_dokumentasi" style="min-height: 50px;">
                                        <li class="list-group-item text-center text-muted small fst-italic py-3">Belum ada file.</li>
                                    </ul>
                                    <div class="p-3 text-center border rounded bg-light dashed-border mt-auto">
                                        <i class="bi bi-cloud-arrow-up text-info fs-3"></i>
                                        <p class="small mb-0 text-muted">Drag & Drop atau Klik di sini</p>
                                    </div>
                                    <input type="file" class="d-none" id="edit_files_dokumentasi" name="files_dokumentasi[]" multiple>
                                </div>
                            </div>
                        </div>

                        {{-- Kategori 4: Lainnya --}}
                        <div class="col-md-6">
                            <div class="card h-100 border-secondary shadow-sm upload-zone position-relative" data-target="edit_files_lainnya" style="cursor: pointer; transition: all 0.2s;">
                                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-paperclip me-2"></i>Lainnya</span>
                                    <span class="badge bg-white text-secondary rounded-pill" id="count-edit_files_lainnya">0</span>
                                </div>
                                <div class="card-body p-2 d-flex flex-column">
                                    <ul class="list-group list-group-flush mb-2 flex-grow-1" id="list-edit_files_lainnya" style="min-height: 50px;">
                                        <li class="list-group-item text-center text-muted small fst-italic py-3">Belum ada file.</li>
                                    </ul>
                                    <div class="p-3 text-center border rounded bg-light dashed-border mt-auto">
                                        <i class="bi bi-cloud-arrow-up text-secondary fs-3"></i>
                                        <p class="small mb-0 text-muted">Drag & Drop atau Klik di sini</p>
                                    </div>
                                    <input type="file" class="d-none" id="edit_files_lainnya" name="files_lainnya[]" multiple>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Elemen untuk menampilkan pesan error ukuran file --}}
                    <div id="edit-files-error" class="invalid-feedback" style="display: none;"></div>
                    <small class="form-text text-muted mt-2 d-block">File baru akan ditambahkan. Klik ikon sampah untuk menghapus file lama.</small>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning">Update</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL BARU: Konfirmasi Waktu Lampau --}}
<div class="modal fade" id="pastTimeConfirmModal" tabindex="-1" aria-labelledby="pastTimeConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pastTimeConfirmModalLabel"><i class="bi bi-exclamation-triangle-fill text-warning"></i> Konfirmasi Waktu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Waktu rapat yang Anda masukkan sudah berlalu. Apakah Anda yakin ingin melanjutkannya?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning" id="confirmPastTimeBtn">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL BARU: Konfirmasi dan Error untuk QR Code --}}
<div class="modal fade" id="qrConfirmModal" tabindex="-1" aria-labelledby="qrConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" id="qrConfirmModalHeader">
                <h5 class="modal-title" id="qrConfirmModalLabel">
                    {{-- Judul akan diisi oleh JS --}}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="qrConfirmModalBody">
                {{-- Pesan akan diisi oleh JS --}}
            </div>
            <div class="modal-footer" id="qrConfirmModalFooter">
                {{-- Tombol akan diisi oleh JS --}}
            </div>
        </div>
    </div>
</div>

{{-- MODAL BARU: Konfirmasi Hapus File --}}
<div class="modal fade" id="deleteFileConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Konfirmasi Hapus File</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus file ini? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteFileBtn">Hapus</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    /* CSS Flatpickr dihapus */
    .status-indicator {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 8px;
    }
    .status-available { background-color: #28a745; } /* Hijau */
    .status-unavailable { background-color: #dc3545; } /* Merah */

    /* Style untuk option yang disabled */
    select option:disabled {
        color: #adb5bd;
        background-color: #e9ecef;
    }

    /* Gaya modern untuk modal konfirmasi */
    #pastTimeConfirmModal .modal-content {
        /* Menambahkan bayangan agar lebih menonjol */
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        border: 5px solid rgba(255, 193, 7, 0.5); /* Outline kuning (warning) */
    }

    /* Menggelapkan dan memberikan efek blur pada backdrop */
    .modal-backdrop.show {
        /* Opasitas default adalah 0.5, kita naikkan menjadi 0.7 */
        opacity: 0.7; 
        
        /* Efek blur modern (jika didukung browser) */
        -webkit-backdrop-filter: blur(5px);
        backdrop-filter: blur(5px);
    }

    .file-item-actions {
        /* Efek blur modern (jika didukung browser) */
        -webkit-backdrop-filter: blur(5px);
        backdrop-filter: blur(5px);
    }

    /* Gaya untuk highlight baris tabel */
    .table-row-highlight {
        animation: highlight-fade 4s ease-out;
    }

    @keyframes highlight-fade {
        0%, 50% { background-color: rgba(255, 193, 7, 0.4); } /* Mulai & tahan warna kuning */
        100% { background-color: transparent; } /* Pudar ke transparan */
    }

    /* Drag & Drop Styles */
    .drop-zone {
        border: 2px dashed #ccc;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
        cursor: pointer;
        position: relative;
    }

    .drop-zone:hover {
        border-color: #0d6efd;
        background-color: #e9ecef;
    }

    /* Color Modifiers */
    .drop-zone--primary {
        border-color: #0d6efd;
        background-color: #f1f8ff; /* Very light blue */
    }
    .drop-zone--primary:hover, .drop-zone--primary.drop-zone--over {
        background-color: #e7f1ff;
    }
    .drop-zone--primary .drop-zone__prompt i { color: #0d6efd; }

    .drop-zone--success {
        border-color: #198754;
        background-color: #f0fff4; /* Very light green */
    }
    .drop-zone--success:hover, .drop-zone--success.drop-zone--over {
        background-color: #e6fffa;
    }
    .drop-zone--success .drop-zone__prompt i { color: #198754; }

    .drop-zone--info {
        border-color: #0dcaf0;
        background-color: #f0faff; /* Very light cyan */
    }
    .drop-zone--info:hover, .drop-zone--info.drop-zone--over {
        background-color: #e0f7fa;
    }
    .drop-zone--info .drop-zone__prompt i { color: #0dcaf0; }

    .drop-zone--secondary {
        border-color: #6c757d;
        background-color: #f8f9fa; /* Light grey */
    }
    .drop-zone--secondary:hover, .drop-zone--secondary.drop-zone--over {
        background-color: #e9ecef;
    }
    .drop-zone--secondary .drop-zone__prompt i { color: #6c757d; }


    .drop-zone--over {
        border-color: #0d6efd;
        background-color: #e9ecef;
        transform: scale(1.02);
    }

    .drop-zone__input {
        display: none;
    }

    .drop-zone__prompt {
        font-size: 0.75rem;
        color: #6c757d;
        line-height: 1.2;
    }
    
    .drop-zone__prompt i {
        font-size: 1.5rem;
        display: block;
        margin-bottom: 5px;
        color: #0d6efd;
    }

    .file-list-scrollable {
        max-height: 100px;
        overflow-y: auto;
        margin-bottom: 0.5rem;
    }
    
    .file-list-scrollable::-webkit-scrollbar {
        width: 4px;
    }
    .file-list-scrollable::-webkit-scrollbar-track {
        background: #f1f1f1; 
    }
    .file-list-scrollable::-webkit-scrollbar-thumb {
        background: #ccc; 
        border-radius: 2px;
    }

    .file-list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 5px 10px;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        margin-top: 5px;
        font-size: 0.85rem;
    }

    .file-list-item .btn-remove {
        color: #dc3545;
        cursor: pointer;
        background: none;
        border: none;
        padding: 0;
        margin-left: 10px;
    }
</style>
@endpush

@push('scripts')
{{-- JS Flatpickr dihapus --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Inisialisasi semua tooltip di halaman
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  })

  document.addEventListener('DOMContentLoaded', function () {
    const allRapats = @json($allRapats);
    const editModalEl = document.getElementById('editRapatModal');
    const addModalEl = document.getElementById('addRapatModal');

    // --- SWEETALERT NOTIFICATION ---
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: "{{ session('error') }}",
        });
    @endif

    // --- FUNGSI UNTUK HIGHLIGHT BARIS BARU/EDIT ---
    const highlightedId = "{{ session('highlight_id') }}";
    if (highlightedId) {
        const row = document.querySelector(`tr[data-rapat-id='${highlightedId}']`);
        if (row) {
            // Scroll ke baris dan berikan highlight
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            row.classList.add('table-row-highlight');
        }
    }


    // --- FUNGSI UNTUK AUTO-REFRESH RUANGAN BERDASARKAN CABANG ---
    async function updateRoomOptions(cabangSelect, roomSelect) {
        const cabangId = cabangSelect.value;
        const originalRoomId = roomSelect.dataset.originalValue || '';
        
        // Kosongkan dan nonaktifkan pilihan ruangan
        roomSelect.innerHTML = '<option value="">-- Pilih Ruangan --</option>';
        roomSelect.disabled = true;
        
        // Tampilkan helper text
        const modal = roomSelect.closest('.modal');
        const helpText = modal.querySelector('.room-help-text');
        if (helpText) {
            helpText.textContent = 'Memuat ruangan...';
            helpText.style.display = 'block';
        }
        
        if (!cabangId) {
            if (helpText) {
                helpText.textContent = 'Pilih cabang terlebih dahulu.';
            }
            return;
        }

        try {
            // Ambil data ruangan dari route web
            const response = await fetch(`{{ url('/cabang') }}/${cabangId}/rooms`);
            if (!response.ok) throw new Error('Gagal mengambil data ruangan');
            
            const rooms = await response.json();

            if (rooms.length === 0) {
                if (helpText) {
                    helpText.textContent = 'Tidak ada ruangan tersedia untuk cabang ini.';
                }
                return;
            }

            // Isi select ruangan dengan data baru
            rooms.forEach(room => {
                const option = new Option(room.room, room.id_room);
                // Akses status_ruangan_id dari relasi statusRuangan
                option.dataset.statusId = room.status_ruangan ? room.status_ruangan.id : room.status_ruangan_id;
                option.dataset.roomName = room.room;
                roomSelect.add(option);
            });

            // Set ke nilai awal jika ada (untuk modal edit)
            if (originalRoomId) {
                roomSelect.value = originalRoomId;
            }
            
            const dateInput = modal.querySelector('.date-input');
            const startTimeInput = modal.querySelector('.time-input[name="waktu_start"]');
            
            // Aktifkan room select setelah data dimuat
            if (dateInput.value && startTimeInput.value) {
                // Jika tanggal dan waktu sudah diisi, langsung aktifkan
                roomSelect.disabled = false;
                if (helpText) {
                    helpText.style.display = 'none';
                }
            } else {
                // Jika belum, tampilkan pesan yang lebih friendly
                roomSelect.disabled = false; // Tetap aktifkan, tapi tampilkan peringatan
                if (helpText) {
                    helpText.textContent = 'Isi tanggal & waktu mulai untuk melihat ketersediaan ruangan.';
                    helpText.classList.remove('text-danger');
                    helpText.classList.add('text-warning');
                    helpText.style.display = 'block';
                }
            }

        } catch (error) {
            console.error('Error fetching rooms:', error);
            if (helpText) {
                helpText.textContent = 'Gagal memuat ruangan. Silakan coba lagi.';
            }
            alert('Terjadi kesalahan saat memuat data ruangan.');
        }
    }

    // Fungsi untuk mengecek ketersediaan ruangan
    function checkRoomAvailability(modal) {
        const dateInput = modal.querySelector('.date-input');
        const startTimeInput = modal.querySelector('.time-input[name="waktu_start"]');
        const endTimeInput = modal.querySelector('.time-input[name="waktu_end"]');
        const roomSelect = modal.querySelector('.room-select');
        const roomHelpText = modal.querySelector('.room-help-text');
        const currentRapatId = modal.querySelector('input[name="id_rapat"]') ? modal.querySelector('input[name="id_rapat"]').value : null;

        clearError(roomSelect);

        const selectedDate = dateInput.value;
        const selectedStartTime = startTimeInput.value;
        const selectedEndTime = endTimeInput.value;

        // Cek apakah ruangan sudah ada isinya (dari hasil load cabang)
        const hasRoomOptions = roomSelect.options.length > 1; // Lebih dari 1 karena ada placeholder

        // Logika untuk mengaktifkan/menonaktifkan dropdown ruangan
        if (!selectedDate || !selectedStartTime) {
            // Hanya disable jika memang belum ada ruangan yang di-load
            if (!hasRoomOptions) {
                roomSelect.disabled = true;
                roomHelpText.style.display = 'block';
            } else {
                // Jika sudah ada ruangan, tetap tampilkan help text tapi jangan disable
                roomHelpText.textContent = 'Pilih tanggal & waktu mulai untuk melihat ketersediaan ruangan.';
                roomHelpText.classList.remove('text-danger');
                roomHelpText.classList.add('text-warning');
                roomHelpText.style.display = 'block';
            }
            return;
        } else {
            // Jika tanggal dan waktu sudah diisi, aktifkan dropdown dan sembunyikan help text
            if (hasRoomOptions) {
                roomSelect.disabled = false;
                roomHelpText.style.display = 'none';
            }
        }

        const selectedStartDateTime = new Date(`${selectedDate}T${selectedStartTime}`);
        const selectedEndDateTime = selectedEndTime ? new Date(`${selectedDate}T${selectedEndTime}`) : new Date(selectedStartDateTime.getTime() + 60 * 60 * 1000);

        // Iterasi setiap opsi ruangan
        Array.from(roomSelect.options).forEach(option => {
            if (!option.value) return;

            const roomId = option.value;
            const staticStatusId = option.getAttribute('data-status-id');

            let isAvailable = true;
            let reason = '';

            // Cek status bawaan ruangan
            if (staticStatusId == 2) {
                isAvailable = false;
                reason = ' (Status: Tidak Tersedia)';
            }

            // Cek jadwal tumpang tindih
            if (isAvailable) {
                for (const rapat of allRapats) {
                    if (rapat.id_rapat == currentRapatId) continue;

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

    // --- VALIDASI FORM ---
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
                showError(input, `Kolom ${input.previousElementSibling.textContent.replace('*', '').trim()} tidak boleh kosong.`);
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

    // Event listener untuk form submit
    document.getElementById('addRapatForm').addEventListener('submit', function(e) {
        if (!validateForm(this)) e.preventDefault();
    });
    
    document.getElementById('editRapatForm').addEventListener('submit', function(e) {
        if (!validateForm(this)) e.preventDefault();
    });

    // --- EVENT LISTENER UNTUK PERUBAHAN CABANG ---
    // Untuk modal Add
    document.getElementById('add_id_cabang').addEventListener('change', function() {
        const roomSelect = document.getElementById('add_id_room');
        roomSelect.dataset.originalValue = '';
        updateRoomOptions(this, roomSelect).then(() => {
            // Setelah ruangan di-load, cek ketersediaan
            checkRoomAvailability(addModalEl);
        });
    });

    // Untuk modal Edit
    document.getElementById('edit_id_cabang').addEventListener('change', function() {
        const roomSelect = document.getElementById('edit_id_room');
        roomSelect.dataset.originalValue = '';
        updateRoomOptions(this, roomSelect).then(() => {
            // Setelah ruangan di-load, cek ketersediaan
            checkRoomAvailability(editModalEl);
        });
    });

    // --- HELPER FUNCTION FOR FILE ICONS (GLOBAL SCOPE) ---
    function getFileIcon(fileType) {
        if (!fileType) return '<i class="bi bi-file-earmark-text text-secondary me-2"></i>';
        if (fileType.includes('pdf')) return '<i class="bi bi-file-earmark-pdf text-danger me-2"></i>';
        if (fileType.includes('word') || fileType.includes('doc')) return '<i class="bi bi-file-earmark-word text-primary me-2"></i>';
        if (fileType.includes('presentation') || fileType.includes('powerpoint') || fileType.includes('ppt')) return '<i class="bi bi-file-earmark-ppt text-warning me-2"></i>';
        if (fileType.includes('spreadsheet') || fileType.includes('excel') || fileType.includes('xls')) return '<i class="bi bi-file-earmark-excel text-success me-2"></i>';
        if (fileType.includes('image')) return '<i class="bi bi-file-earmark-image text-info me-2"></i>';
        if (fileType.includes('zip') || fileType.includes('compressed')) return '<i class="bi bi-file-earmark-zip text-dark me-2"></i>';
        return '<i class="bi bi-file-earmark-text text-secondary me-2"></i>';
    }

    // --- DRAG AND DROP LOGIC (UPDATED FOR CARDS) ---
    const uploadZones = document.querySelectorAll('.upload-zone');

    uploadZones.forEach(zone => {
        const inputId = zone.getAttribute('data-target');
        const input = document.getElementById(inputId);
        const list = document.getElementById(`list-${inputId}`);
        const countBadge = document.getElementById(`count-${inputId}`);
        
        let dataTransfer = new DataTransfer();

        // Click to upload (delegated)
        zone.addEventListener('click', (e) => {
            // Ignore clicks on links or delete buttons
            if (e.target.closest('a') || e.target.closest('.btn-delete-file')) {
                return;
            }
            input.click();
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
                handleFiles(e.dataTransfer.files);
            }
        });

        // Input change
        input.addEventListener('change', (e) => {
            if (input.files.length > 0) {
                handleFiles(input.files);
            }
        });

        function handleFiles(files) {
            const maxFileSize = 20 * 1024 * 1024; // 20MB
            const allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'xls', 'xlsx', 'zip', 'rar', '7z', 'mp4', 'mp3', 'wav'];
            const oversizedFiles = [];
            const invalidTypeFiles = [];

            Array.from(files).forEach(file => {
                const fileExtension = file.name.split('.').pop().toLowerCase();

                if (file.size > maxFileSize) {
                    oversizedFiles.push(file.name);
                } else if (!allowedExtensions.includes(fileExtension)) {
                    invalidTypeFiles.push(file.name);
                } else {
                    dataTransfer.items.add(file);
                }
            });

            // Update input files
            input.files = dataTransfer.files;
            renderList();

            // Show errors if any
            let errorMessage = '';
            if (oversizedFiles.length > 0) {
                errorMessage += `File terlalu besar (>20MB): ${oversizedFiles.join(', ')}. `;
            }
            if (invalidTypeFiles.length > 0) {
                errorMessage += `Tipe file tidak didukung: ${invalidTypeFiles.join(', ')}. `;
            }

            if (errorMessage) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Bermasalah',
                    text: errorMessage
                });
            }
        }

        function renderList() {
            // Clear only new files (keep existing files if any, but logic needs to be careful)
            // Actually, for simplicity, we append new files to the list.
            // But wait, the list might contain existing files (loaded from server).
            // We need to distinguish between existing files and new files.
            // The existing files are rendered by the 'show.bs.modal' event listener.
            // So here we should only render the NEW files from dataTransfer.
            
            // Strategy: Clear the list, re-render existing files (if we stored them), then render new files.
            // OR: Keep existing files in the DOM, and only manage new files.
            // Let's try to keep existing files and append new ones.
            
            // First, remove all items that are NOT existing files (marked with data-existing="true")
            const existingItems = Array.from(list.children).filter(li => li.dataset.existing === "true");
            list.innerHTML = '';
            existingItems.forEach(item => list.appendChild(item));

            // Update badge count (existing + new)
            countBadge.textContent = existingItems.length + dataTransfer.files.length;

            if (existingItems.length === 0 && dataTransfer.files.length === 0) {
                list.innerHTML = '<li class="list-group-item text-center text-muted small fst-italic py-3">Belum ada file.</li>';
                return;
            }

            Array.from(dataTransfer.files).forEach((file, index) => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center small py-1';
                
                const fileInfo = document.createElement('span');
                fileInfo.innerHTML = `<i class="bi bi-file-earmark me-2"></i> ${file.name} <small class="text-muted ms-1">(${Math.round(file.size/1024)} KB)</small> <span class="badge bg-info text-dark ms-1">Baru</span>`;

                const deleteBtn = document.createElement('button');
                deleteBtn.type = 'button';
                deleteBtn.className = 'btn btn-link text-danger p-0 btn-delete-file';
                deleteBtn.title = 'Hapus';
                deleteBtn.innerHTML = '<i class="bi bi-x-circle"></i>';
                deleteBtn.onclick = (e) => {
                    e.stopPropagation(); // Prevent card click
                    const newFiles = new DataTransfer();
                    Array.from(dataTransfer.files).forEach((f, i) => {
                        if (i !== index) newFiles.items.add(f);
                    });
                    dataTransfer = newFiles;
                    input.files = newFiles.files;
                    renderList();
                };

                li.appendChild(fileInfo);
                li.appendChild(deleteBtn);
                list.appendChild(li);
            });
        }
        
        // Expose renderList to be called externally (e.g. when clearing form)
        input.renderList = renderList;
        input.clearFiles = () => {
            dataTransfer = new DataTransfer();
            input.files = dataTransfer.files;
            renderList();
        };
    });

    // Event listener untuk perubahan tanggal/waktu
    ['add_tanggal', 'add_waktu_start', 'add_waktu_end'].forEach(id => {
        document.getElementById(id).addEventListener('change', function() {
            checkRoomAvailability(addModalEl);
        });
    });

    ['edit_tanggal', 'edit_waktu_start', 'edit_waktu_end'].forEach(id => {
        document.getElementById(id).addEventListener('change', function() {
            checkRoomAvailability(editModalEl);
        });
    });

    // Event listener untuk modal edit
    editModalEl.addEventListener('show.bs.modal', async function (event) {
        const button = event.relatedTarget;
        const rapat = JSON.parse(button.getAttribute('data-rapat'));
        const form = document.getElementById('editRapatForm');
        
        const cabangSelect = document.getElementById('edit_id_cabang');
        const roomSelect = document.getElementById('edit_id_room');

        form.action = `{{ url('meetings') }}/${rapat.id_rapat}`;
        
        document.getElementById('edit_id_rapat').value = rapat.id_rapat;
        document.getElementById('edit_judul').value = rapat.judul;
        document.getElementById('edit_tanggal').value = rapat.tanggal;
        document.getElementById('edit_desc').value = rapat.desc || '';
        document.getElementById('edit_waktu_start').value = rapat.waktu_start ? rapat.waktu_start.substring(0, 5) : '';
        document.getElementById('edit_waktu_end').value = rapat.waktu_end ? rapat.waktu_end.substring(0, 5) : '';
        document.getElementById('edit_id_status').value = rapat.id_status;

        cabangSelect.value = rapat.id_cabang;
        roomSelect.dataset.originalValue = rapat.id_room;

        updateRoomOptions(cabangSelect, roomSelect).then(() => {
            checkRoomAvailability(editModalEl);
        });

        // Ambil dan tampilkan file secara dinamis berdasarkan kategori
        const listMateri = document.getElementById('list-edit_files_materi');
        const listNotulensi = document.getElementById('list-edit_files_notulensi');
        const listDokumentasi = document.getElementById('list-edit_files_dokumentasi');
        const listLainnya = document.getElementById('list-edit_files_lainnya');
        
        const countMateri = document.getElementById('count-edit_files_materi');
        const countNotulensi = document.getElementById('count-edit_files_notulensi');
        const countDokumentasi = document.getElementById('count-edit_files_dokumentasi');
        const countLainnya = document.getElementById('count-edit_files_lainnya');

        // Helper to reset list
        const resetList = (list, count) => {
            if(list) list.innerHTML = '<li class="list-group-item text-muted small py-1 fst-italic text-center">Memuat...</li>';
            if(count) count.textContent = '0';
        };

        resetList(listMateri, countMateri);
        resetList(listNotulensi, countNotulensi);
        resetList(listDokumentasi, countDokumentasi);
        resetList(listLainnya, countLainnya);

        // Helper to add file to list
        const addFileToUI = (file, list, countElement) => {
            if (!list) return;
            
            // Remove "Belum ada file" or "Memuat..." message if it exists
            if (list.querySelector('.text-muted.text-center')) {
                list.innerHTML = '';
            }

            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center small py-1';
            li.dataset.fileId = file.id_file;
            li.dataset.existing = "true"; // Mark as existing file

            // Link File
            const fileLink = document.createElement('a');
            const downloadUrl = `{{ route('meetings.downloadFile', ['file' => ':fileId']) }}`.replace(':fileId', file.id_file);
            fileLink.href = downloadUrl;
            fileLink.target = '_blank';
            fileLink.rel = 'noopener noreferrer';
            
            const iconHTML = getFileIcon(file.file_type || '');
            fileLink.innerHTML = `${iconHTML} ${file.file_name}`;
            
            li.appendChild(fileLink);

            // Tombol Hapus
            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'btn btn-link text-danger p-0 btn-delete-file';
            deleteBtn.title = 'Hapus';
            deleteBtn.innerHTML = '<i class="bi bi-trash"></i>';
            deleteBtn.onclick = (e) => {
                e.stopPropagation(); // Prevent card click
                deleteFile(file.id_file, li, countElement);
            };

            li.appendChild(deleteBtn);
            list.appendChild(li);
            
            // Update count
            if(countElement) {
                countElement.textContent = parseInt(countElement.textContent || 0) + 1;
            }
        };

        const renderFiles = (files) => {
             // Kosongkan list sebelum mengisi
            [listMateri, listNotulensi, listDokumentasi, listLainnya].forEach(list => {
                if(list) list.innerHTML = '';
            });

            if (files && files.length > 0) {
                files.forEach(file => {
                    switch(parseInt(file.id_categories)) {
                        case 1: addFileToUI(file, listMateri, countMateri); break;
                        case 2: addFileToUI(file, listNotulensi, countNotulensi); break;
                        case 3: addFileToUI(file, listDokumentasi, countDokumentasi); break;
                        case 4: addFileToUI(file, listLainnya, countLainnya); break;
                        default: addFileToUI(file, listLainnya, countLainnya);
                    }
                });
            }
            
            // Restore "Belum ada file" if empty
            [
                {list: listMateri, count: countMateri}, 
                {list: listNotulensi, count: countNotulensi}, 
                {list: listDokumentasi, count: countDokumentasi}, 
                {list: listLainnya, count: countLainnya}
            ].forEach(({list, count}) => {
                if(list && list.children.length === 0) {
                    list.innerHTML = '<li class="list-group-item text-center text-muted small fst-italic py-3">Belum ada file.</li>';
                }
            });
        };

        // Hybrid Loading Strategy:
        // 1. Try to use eager-loaded files from data-rapat
        // 2. If missing, fallback to AJAX fetch
        if (rapat.files && Array.isArray(rapat.files)) {
            console.log('Using pre-loaded files:', rapat.files);
            renderFiles(rapat.files);
        } else {
            console.log('Pre-loaded files missing, fetching from server...');
            try {
                const response = await fetch(`{{ url('meetings') }}/${rapat.id_rapat}/files`);
                if (!response.ok) throw new Error('Gagal memuat file.');
                const files = await response.json();
                renderFiles(files);
            } catch (error) {
                console.error('Error fetching files:', error);
                [listMateri, listNotulensi, listDokumentasi, listLainnya].forEach(list => {
                    if(list) list.innerHTML = '<li class="list-group-item text-danger small py-1 text-center">Gagal memuat file.</li>';
                });
            }
        }
    });

    // Fungsi untuk menghapus file via AJAX (Updated with Modal)
    window.deleteFile = function(fileId, listItemElement, countElement) {
        const deleteModalEl = document.getElementById('deleteFileConfirmModal');
        const deleteModal = new bootstrap.Modal(deleteModalEl);
        const confirmBtn = document.getElementById('confirmDeleteFileBtn');

        // Simpan data ke tombol konfirmasi
        confirmBtn.onclick = async function() {
            try {
                // Tampilkan loading state di tombol
                const originalBtnText = this.innerHTML;
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menghapus...';
                this.disabled = true;

                const response = await fetch(`{{ url('meetings/files') }}/${fileId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const result = await response.json();
                
                if (result.success) {
                    listItemElement.remove(); // Hapus item dari list di UI
                    
                    // Update count if element provided
                    if(countElement) {
                        const currentCount = parseInt(countElement.textContent || 0);
                        if(currentCount > 0) {
                            countElement.textContent = currentCount - 1;
                        }
                    }

                    deleteModal.hide(); // Tutup modal
                } else { 
                    throw new Error(result.message); 
                }
            } catch (error) {
                console.error('Error deleting file:', error);
                alert('Gagal menghapus file. Silakan coba lagi.');
            } finally {
                // Reset tombol
                this.innerHTML = 'Hapus';
                this.disabled = false;
            }
        };

        deleteModal.show();
    };

    // Reset forms when modal is closed
    // Clear focus BEFORE modal closes to prevent aria-hidden warning
    addModalEl.addEventListener('hide.bs.modal', function () {
        // Remove focus from all focusable elements inside the modal
        const focusableElements = addModalEl.querySelectorAll('input, button, select, textarea, a[href], [tabindex]:not([tabindex="-1"])');
        focusableElements.forEach(el => el.blur());
        
        // Move focus to body
        document.body.focus();
    });
    
    // Clean up AFTER modal is closed
    addModalEl.addEventListener('hidden.bs.modal', function () {
        document.getElementById('addRapatForm').reset();
        // Clear file lists
        ['add_files_materi', 'add_files_notulensi', 'add_files_dokumentasi', 'add_files_lainnya'].forEach(id => {
            const input = document.getElementById(id);
            if(input && input.clearFiles) input.clearFiles();
        });
    });

    // Clear focus BEFORE edit modal closes to prevent aria-hidden warning
    editModalEl.addEventListener('hide.bs.modal', function () {
        // Remove focus from all focusable elements inside the modal
        const focusableElements = editModalEl.querySelectorAll('input, button, select, textarea, a[href], [tabindex]:not([tabindex="-1"])');
        focusableElements.forEach(el => el.blur());
        
        // Move focus to body
        document.body.focus();
    });

    // Clean up AFTER edit modal is closed
    editModalEl.addEventListener('hidden.bs.modal', function () {
        document.getElementById('editRapatForm').reset();
        // Clear file lists
         ['edit_files_materi', 'edit_files_notulensi', 'edit_files_dokumentasi', 'edit_files_lainnya'].forEach(id => {
            const input = document.getElementById(id);
            if(input && input.clearFiles) input.clearFiles();
            // Also clear existing files from DOM
            const list = document.getElementById(`list-${id}`);
            if(list) list.innerHTML = '<li class="list-group-item text-center text-muted small fst-italic py-3">Belum ada file.</li>';
            const badge = document.getElementById(`count-${id}`);
            if(badge) badge.textContent = '0';
        });
    });


    // Clear error saat user mengetik
    document.querySelectorAll('#addRapatModal input, #addRapatModal select, #editRapatModal input, #editRapatModal select').forEach(input => {
        const eventType = input.tagName === 'SELECT' ? 'change' : 'input';
        input.addEventListener(eventType, (e) => {
            clearError(e.target);
        });
    });

    // SKRIP BARU: Live search dengan debounce untuk judul rapat
    const searchInput = document.getElementById('search-input');
    const filterForm = document.getElementById('filter-form');
    let debounceTimer;

    searchInput.addEventListener('input', function (e) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            if (filterForm) {
                // PERBAIKAN: Buat URL secara manual untuk memastikan semua parameter ada
                const formData = new FormData(filterForm);
                const params = new URLSearchParams();

                // Tambahkan semua field form ke parameter, termasuk id_user_pic yang sudah terpilih
                for (const pair of formData.entries()) {
                    if (pair[1]) params.append(pair[0], pair[1]);
                }
                window.location.href = `{{ route('meetings.index') }}?${params.toString()}`;
            }
        }, 500); // Tunggu 500ms setelah pengguna berhenti mengetik
    });

    // Hapus script lama yang menangani redirect dari server
    // @if (session('confirm_past_time_update')) ... @endif

    // Logika baru untuk konfirmasi waktu lampau dengan modal
    const pastTimeConfirmModalEl = document.getElementById('pastTimeConfirmModal');
    const pastTimeConfirmModal = new bootstrap.Modal(pastTimeConfirmModalEl);
    const confirmPastTimeBtn = document.getElementById('confirmPastTimeBtn');
    let formToSubmit; // Variabel untuk menyimpan form yang akan di-submit

    document.getElementById('editRapatForm').addEventListener('submit', function(e) {
        e.preventDefault(); // Selalu hentikan submit default terlebih dahulu

        formToSubmit = this; // Simpan form saat ini
        const dateInput = formToSubmit.querySelector('#edit_tanggal');
        const startTimeInput = formToSubmit.querySelector('#edit_waktu_start');

        // Cek apakah waktu berada di masa lampau
        const selectedDateTime = new Date(`${dateInput.value}T${startTimeInput.value}`);
        const now = new Date();

        if (selectedDateTime < now) {
            // Jika waktu di masa lampau, tampilkan modal konfirmasi
            pastTimeConfirmModal.show();
        } else {
            // Jika waktu tidak di masa lampau, langsung submit form
            formToSubmit.submit();
        }
    });

    // Tambahkan event listener untuk tombol "Ya, Lanjutkan" di modal
    confirmPastTimeBtn.addEventListener('click', function() {
        if (formToSubmit) {
            formToSubmit.submit(); // Submit form yang sudah disimpan
        }
    });

    // SKRIP BARU: Menampilkan loading screen saat form di-submit
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        // Untuk form tambah rapat
        document.getElementById('addRapatForm').addEventListener('submit', function(e) {
            // Hanya tampilkan loading jika form valid dan akan di-submit
            if (validateForm(this)) {
                loadingOverlay.style.display = 'flex';
            }
        });

        // Untuk semua form hapus
        document.querySelectorAll('.delete-meeting-form').forEach(form => {
            form.addEventListener('submit', function() {
                loadingOverlay.style.display = 'flex';
            });
        });
    }

    // --- SKRIP BARU: Logika untuk Modal Konfirmasi QR Code ---
    const qrConfirmModalEl = document.getElementById('qrConfirmModal');
    const qrConfirmModal = new bootstrap.Modal(qrConfirmModalEl);
    const qrModalHeader = document.getElementById('qrConfirmModalHeader');
    const qrModalLabel = document.getElementById('qrConfirmModalLabel');
    const qrModalBody = document.getElementById('qrConfirmModalBody');
    const qrModalFooter = document.getElementById('qrConfirmModalFooter');

    document.querySelectorAll('.qr-code-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            const status = this.getAttribute('data-rapat-status');
            const qrUrl = this.getAttribute('data-qr-url');

            // Reset tampilan modal
            qrModalHeader.className = 'modal-header';
            qrModalFooter.innerHTML = '';

            if (status.toLowerCase() === 'berlangsung') {
                // Tampilan untuk konfirmasi
                qrModalHeader.classList.add('bg-primary', 'text-white');
                qrModalLabel.innerHTML = `<i class="bi bi-patch-question-fill me-2"></i> Konfirmasi Pembuatan QR Code`;
                qrModalBody.textContent = 'Anda akan membuat QR Code untuk rapat ini. Apakah Anda yakin ingin melanjutkan?';

                const continueBtn = document.createElement('button');
                continueBtn.type = 'button';
                continueBtn.className = 'btn btn-primary';
                continueBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i> Ya, Lanjutkan';
                continueBtn.onclick = () => {
                    window.open(qrUrl, '_blank');
                    qrConfirmModal.hide();
                };

                const cancelBtn = document.createElement('button');
                cancelBtn.type = 'button';
                cancelBtn.className = 'btn btn-secondary';
                cancelBtn.textContent = 'Batal';
                cancelBtn.setAttribute('data-bs-dismiss', 'modal');

                qrModalFooter.appendChild(cancelBtn);
                qrModalFooter.appendChild(continueBtn);
            } else {
                // Tampilan untuk error
                qrModalHeader.classList.add('bg-danger', 'text-white');
                qrModalLabel.innerHTML = `<i class="bi bi-x-octagon-fill me-2"></i> Aksi Ditolak`;
                qrModalBody.textContent = `QR Code baru bisa dibuat jika status rapat telah "Berlangsung". Status saat ini adalah "${status}".`;
            }
            qrConfirmModal.show();
        });
    });
});
</script>
@endpush
