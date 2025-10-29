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

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Manajemen Rapat</h5>
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addRapatModal">
                <i class="bi bi-plus-circle me-1"></i> Tambah Rapat
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <form action="{{ route('meetings.index') }}" method="GET">
                        <div class="input-group">
                            <select name="id_user_pic" id="picSelector" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Pilih PIC --</option>
                                @foreach ($pics as $pic)
                                    <option value="{{ $pic->id_user }}" {{ request('id_user_pic') == $pic->id_user ? 'selected' : '' }}>
                                        {{ $pic->name }}
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
                        <th>Judul</th>
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
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $rapat->judul }}</td>
                            <td>{{ $rapat->cabang ? $rapat->cabang->nama_cabang : 'N/A' }}</td>
                            <td>{{ $rapat->room ? $rapat->room->nama_ruangan : 'N/A' }}</td>
                            <td>{{ \Carbon\Carbon::parse($rapat->tanggal)->translatedFormat('d F Y') }}</td>
                            <td>{{ substr($rapat->waktu_mulai, 0, 5) }} - {{ substr($rapat->waktu_selesai, 0, 5) }}</td>
                            <td><span class="badge bg-info">{{ $rapat->status ? $rapat->status->status_rapat : 'N/A' }}</span></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton{{ $rapat->id_rapat }}" data-bs-toggle="dropdown" aria-expanded="false">Aksi</button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $rapat->id_rapat }}">
                                        <li><a class="dropdown-item edit-btn" href="#" data-bs-toggle="modal" data-bs-target="#editRapatModal" data-rapat='{{ json_encode($rapat) }}'>Edit</a></li>
                                        <li><a class="dropdown-item" href="{{ route('meetings.showQr', $rapat->id_rapat) }}" target="_blank">QR Code</a></li>
                                        <li>
                                            <form action="{{ route('meetings.destroy', $rapat->id_rapat) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item" onclick="return confirm('Anda yakin ingin menghapus rapat ini?')">Hapus</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                @if(request('id_user_pic'))
                                    Tidak ada data rapat untuk PIC yang dipilih.
                                @else
                                    Silakan pilih PIC untuk menampilkan data rapat.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Rapat Modal --}}
<div class="modal fade" id="addRapatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('meetings.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah Rapat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Form fields will be added here --}}
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
        <form action="" method="POST" class="modal-content" id="editRapatForm">
            @csrf
            @method('PUT')
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Edit Rapat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_rapat" id="edit_id_rapat">
                {{-- Form fields will be added here --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Script untuk mengisi modal edit
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('editRapatModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const rapat = JSON.parse(button.getAttribute('data-rapat'));
            const form = document.getElementById('editRapatForm');
            // Gunakan URL yang benar untuk update
            form.action = `{{ url('admin/meetings') }}/${rapat.id_rapat}`;
            document.getElementById('edit_id_rapat').value = rapat.id_rapat;
            // Populate other form fields here based on the 'rapat' object
        });
    });
</script>
@endpush
