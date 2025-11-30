@extends('layouts.app')

@section('title', 'Manajemen Cabang & Ruangan')
@section('page-title', 'Manajemen Cabang & Ruangan')

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
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Manajemen Cabang & Ruangan</h5>
            <div>
                <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addBranchModal">
                    <i class="bi bi-pencil-square me-1"></i> Kelola Cabang
                </button>
                <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addBranchModal" style="display: none;">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Cabang
                </button>
                @if(!request('id_cabang'))
                    <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" title="Silahkan memilih Cabang dahulu">
                        <button class="btn btn-light btn-sm" type="button" disabled style="pointer-events: none;">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Ruangan
                        </button>
                    </span>
                @else
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Ruangan
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('branch') }}" method="GET" id="filter-form">
                <div class="row mb-3 g-2">
                    <div class="col-md-6">
                        <select name="id_cabang" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Pilih Cabang untuk filter --</option>
                            @foreach ($cabang as $c)
                                <option value="{{ $c->id }}" {{ request('id_cabang') == $c->id ? 'selected' : '' }}>
                                    {{ $c->cabang }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="search" id="search-input" class="form-control" placeholder="Cari berdasarkan nama ruangan..." value="{{ request('search') }}">
                    </div>
                </div>
            </form>

            <table class="table table-hover mt-3">
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>
                            {{-- Tombol untuk sorting nama ruangan --}}
                            <a href="{{ route('branch', array_merge(request()->query(), ['sort' => 'room', 'direction' => request('direction', 'asc') == 'asc' ? 'desc' : 'asc'])) }}" class="text-black text-decoration-none">
                                Nama Ruangan
                                @if ($sort === 'room.room')
                                    {{-- Ikon jika kolom ini sedang aktif diurutkan --}}
                                    <i class="bi {{ $direction === 'asc' ? 'bi-sort-alpha-down' : 'bi-sort-alpha-up' }}"></i>
                                @else
                                    {{-- Ikon default jika kolom lain yang diurutkan --}}
                                    <i class="bi bi-sort-alpha-down"></i>
                                @endif
                            </a>
                        </th>
                        <th>Cabang</th>
                        <th>
                            {{-- Tombol untuk sorting alamat cabang --}}
                            <a href="{{ route('branch', array_merge(request()->query(), ['sort' => 'cabang.alamat', 'direction' => request('direction', 'asc') == 'asc' ? 'desc' : 'asc'])) }}" class="text-black text-decoration-none">
                                Alamat Cabang
                                @if ($sort === 'cabang.alamat')
                                    {{-- Ikon jika kolom ini sedang aktif diurutkan --}}
                                    <i class="bi {{ $direction === 'asc' ? 'bi-sort-alpha-down' : 'bi-sort-alpha-up' }}"></i>
                                @else
                                    {{-- Ikon default jika kolom lain yang diurutkan --}}
                                    <i class="bi bi-sort-alpha-down"></i>
                                @endif
                            </a>
                        </th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rooms as $room)
                    <tr>
                        <td>{{ $loop->iteration + ($rooms->currentPage() - 1) * $rooms->perPage() }}</td>
                        <td>{{ $room->room }}</td>
                        <td>{{ $room->cabang->cabang ?? 'N/A' }}</td>
                        <td>{{ $room->cabang->alamat ?? 'N/A' }}</td>
                        <td>
                            <span class="badge {{ $room->status_ruangan_id == 1 ? 'bg-success' : 'bg-danger' }}">
                                {{ $room->statusRuangan->nama_status ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning edit-room-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editRoomModal"
                                    data-id="{{ $room->id_room }}"
                                    data-name="{{ $room->room }}"
                                    data-cabang-id="{{ $room->id_cabang }}"
                                    data-status-id="{{ $room->status_ruangan_id }}">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button> 
                            <button type="button" class="btn btn-sm btn-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteRoomModal"
                                    data-name="{{ $room->room }}"
                                    data-url="{{ route('room.delete', $room->id_room) }}">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            @if(request('search') || request('id_cabang'))
                                Tidak ada data ruangan untuk cabang yang dipilih.
                            @else
                                Belum ada data ruangan.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- PAGINATION YANG DIPERBAIKI DAN DISIMPLIFY -->
            <!-- PAGINATION -->
            <div class="d-flex justify-content-end mt-4">
                @if ($rooms instanceof \Illuminate\Pagination\AbstractPaginator)
                    {{ $rooms->appends(request()->query())->links('pagination::simple-bootstrap-5') }}
                @endif
            </div>

        </div>
    </div>
</div>

<!-- Modal Kelola Cabang -->
<div class="modal fade" id="addBranchModal" tabindex="-1" aria-labelledby="addBranchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addBranchModalLabel">Kelola Cabang</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Form Tambah Cabang -->
                <h6 class="mb-3">Tambah Cabang Baru</h6>
                <form action="{{ route('branch.add') }}" method="POST" class="mb-4 p-3 border rounded loading-trigger-form">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md">
                            <input type="text" name="cabang" class="form-control" placeholder="Nama Cabang" required>
                        </div>
                        <div class="col-md">
                            <input type="text" name="alamat" class="form-control" placeholder="Alamat Cabang" required>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-plus-circle"></i> Tambah
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Daftar Cabang yang Ada -->
                <h6 class="mb-2">Daftar Cabang</h6>
                @include('branches.partials.branch-list', ['cabang' => $cabang])
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Ruangan -->
<div class="modal fade" id="addRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('room.add') }}" method="POST" class="modal-content loading-trigger-form">
            @csrf
            <input type="hidden" name="id_cabang" value="{{ request('id_cabang') }}">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah Ruangan Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="room_name">Nama Ruangan</label>
                    <input type="text" name="room" id="room_name" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Ruangan -->
<div class="modal fade" id="editRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('room.edit') }}" method="POST" class="modal-content loading-trigger-form">
            @csrf
            <input type="hidden" name="id_room" id="edit_room_id">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">Edit Ruangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="edit_room_name" class="form-label">Nama Ruangan</label>
                    <input type="text" name="room" id="edit_room_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="edit_id_cabang" class="form-label">Cabang</label>
                    <select name="id_cabang" id="edit_id_cabang" class="form-select" required>
                        <option value="">-- Pilih Cabang --</option>
                        @foreach ($cabang as $c)
                            <option value="{{ $c->id }}">{{ $c->cabang }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="edit_status_ruangan_id" class="form-label">Status Ruangan</label>
                    <select name="status_ruangan_id" id="edit_status_ruangan_id" class="form-select" required>
                        <option value="">-- Pilih Status --</option>
                        @foreach ($statusRuangan as $status)
                            <option value="{{ $status->id }}">{{ $status->nama_status }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Delete Cabang -->
<div class="modal fade" id="deleteBranchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Konfirmasi Hapus Cabang</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus cabang <strong id="delete_branch_name"></strong>?</p>
                <p class="text-danger mb-0"><small>Perhatian: Menghapus cabang akan menghapus seluruh data ruangan yang terkait dengan cabang ini.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteBranchForm" method="POST" class="loading-trigger-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Delete Ruangan -->
<div class="modal fade" id="deleteRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Konfirmasi Hapus Ruangan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus ruangan <strong id="delete_room_name"></strong>?</p>
                    </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteRoomForm" method="POST" class="loading-trigger-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Inisialisasi semua tooltip di halaman
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    // Script untuk modal edit ruangan
    const editRoomModal = document.getElementById('editRoomModal');
    if (editRoomModal) {
        editRoomModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const roomId = button.getAttribute('data-id');
            const roomName = button.getAttribute('data-name');
            const cabangId = button.getAttribute('data-cabang-id');
            const statusId = button.getAttribute('data-status-id');

            const modalForm = editRoomModal.querySelector('form');
            const modalRoomIdInput = editRoomModal.querySelector('#edit_room_id');
            const modalRoomNameInput = editRoomModal.querySelector('#edit_room_name');
            const modalCabangSelect = editRoomModal.querySelector('#edit_id_cabang');
            const modalStatusSelect = editRoomModal.querySelector('#edit_status_ruangan_id');

            modalRoomIdInput.value = roomId;
            modalRoomNameInput.value = roomName;
            modalCabangSelect.value = cabangId;
            modalStatusSelect.value = statusId;
        });
    }

    // Script untuk live search dengan debounce
    const searchInput = document.getElementById('search-input');
    const filterForm = document.getElementById('filter-form');
    let debounceTimer;

    if (searchInput && filterForm) {
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                filterForm.submit();
            }, 500); // Tunggu 500ms setelah pengguna berhenti mengetik
        });
    }

    // Script untuk menampilkan loading overlay saat form di-submit
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        document.querySelectorAll('.loading-trigger-form').forEach(form => {
            form.addEventListener('submit', function() {
                loadingOverlay.style.display = 'flex';
            });
        });
    }


    // Script untuk toggle edit cabang
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-edit-branch')) {
            const btn = e.target.closest('.btn-edit-branch');
            const row = btn.closest('tr');
            
            // Toggle visibility
            row.querySelectorAll('.branch-text').forEach(el => el.classList.add('d-none'));
            row.querySelectorAll('.branch-input').forEach(el => el.classList.remove('d-none'));
            
            btn.classList.add('d-none');
            row.querySelector('.btn-save-branch').classList.remove('d-none');
        }
    });

    // Script untuk modal delete cabang
    const deleteBranchModal = document.getElementById('deleteBranchModal');
    if (deleteBranchModal) {
        deleteBranchModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const branchName = button.getAttribute('data-name');
            const deleteUrl = button.getAttribute('data-url');

            const modalBranchName = deleteBranchModal.querySelector('#delete_branch_name');
            const deleteForm = deleteBranchModal.querySelector('#deleteBranchForm');

            modalBranchName.textContent = branchName;
            deleteForm.action = deleteUrl;
        });
    }

    // Script untuk modal delete ruangan
    const deleteRoomModal = document.getElementById('deleteRoomModal');
    if (deleteRoomModal) {
        deleteRoomModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const roomName = button.getAttribute('data-name');
            const deleteUrl = button.getAttribute('data-url');

            const modalRoomName = deleteRoomModal.querySelector('#delete_room_name');
            const deleteForm = deleteRoomModal.querySelector('#deleteRoomForm');

            modalRoomName.textContent = roomName;
            deleteForm.action = deleteUrl;
        });
    }
});
</script>
@endpush
@endsection