@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Account Management</h5>
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-plus-circle me-1"></i> Tambah Akun
            </button>
        </div>
        <div class="card-body">
            <p class="text-muted">Kelola akun admin dan PIC di sistem absensi rapat.</p>

            <table class="table table-hover mt-3">
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $users = session('users', [
                            ['id' => 1, 'nama' => 'Admin Utama', 'email' => 'admin@example.com', 'role' => 'Super Admin'],
                            ['id' => 2, 'nama' => 'PIC Cabang', 'email' => 'pic@example.com', 'role' => 'PIC'],
                        ]);
                    @endphp
                    @foreach ($users as $u)
                    <tr>
                        <td>{{ $u['id'] }}</td>
                        <td>{{ $u['nama'] }}</td>
                        <td>{{ $u['email'] }}</td>
                        <td>{{ $u['role'] }}</td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                    data-bs-target="#editUserModal"
                                    data-id="{{ $u['id'] }}"
                                    data-nama="{{ $u['nama'] }}"
                                    data-email="{{ $u['email'] }}"
                                    data-role="{{ $u['role'] }}">
                                Edit
                            </button>
                            <form action="{{ route('users.delete') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="id" value="{{ $u['id'] }}">
                                <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Hapus akun ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('users.add') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah Akun</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Nama</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Role</label>
                    <select name="role" class="form-select" required>
                        <option value="Super Admin">Super Admin</option>
                        <option value="PIC">PIC</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('users.edit') }}" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Edit Akun</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Nama</label>
                    <input type="text" name="nama" id="edit_nama" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Role</label>
                    <select name="role" id="edit_role" class="form-select" required>
                        <option value="Super Admin">Super Admin</option>
                        <option value="PIC">PIC</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning">Update</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('editUserModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    document.getElementById('edit_id').value = button.getAttribute('data-id');
    document.getElementById('edit_nama').value = button.getAttribute('data-nama');
    document.getElementById('edit_email').value = button.getAttribute('data-email');
    document.getElementById('edit_role').value = button.getAttribute('data-role');
});
</script>
@endpush
@endsection
