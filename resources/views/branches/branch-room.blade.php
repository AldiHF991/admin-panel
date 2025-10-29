@extends('layouts.app')

@section('content')
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
                <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addRoomModal" {{ request('id_cabang') ? '' : 'disabled' }}>
                    <i class="bi bi-plus-circle me-1"></i> Tambah Ruangan
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <form action="{{ route('branch') }}" method="GET">
                        <div class="input-group">
                            <select name="id_cabang" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Pilih Cabang untuk Filter --</option>
                                @foreach ($cabang as $c)
                                    <option value="{{ $c->id }}" {{ request('id_cabang') == $c->id ? 'selected' : '' }}>
                                        {{ $c->cabang }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <table class="table table-hover mt-3">
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Nama Ruangan</th>
                        <th>Cabang</th>
                        <th>Alamat Cabang</th>
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
                            <form action="{{ route('room.delete', $room->id_room) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ruangan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i> Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            @if(request()->has('id_cabang') && request('id_cabang') != '')
                                Tidak ada data ruangan untuk cabang yang dipilih.
                            @else
                                Silakan pilih cabang untuk menampilkan data ruangan.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-end">
                @if ($rooms instanceof \Illuminate\Pagination\AbstractPaginator)
                    {{ $rooms->links() }}
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
                <form action="{{ route('branch.add') }}" method="POST" class="mb-4 p-3 border rounded">
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
        <form action="{{ route('branch.add') }}" method="POST" class="modal-content">
            {{-- @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah Cabang Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="cabang_name">Nama Cabang</label>
                    <input type="text" name="cabang" id="cabang_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="alamat">Alamat</label>
                    <input type="text" name="alamat" id="alamat" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div> --}}
        </form>
    </div>
</div>

<!-- Modal Tambah Ruangan -->
<div class="modal fade" id="addRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('room.add') }}" method="POST" class="modal-content">
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
        <form action="{{ route('room.edit') }}" method="POST" class="modal-content">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
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
});
</script>
@endpush
@endsection