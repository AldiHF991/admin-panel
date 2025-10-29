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
            <h5 class="mb-0">Account Management</h5>
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-plus-circle me-1"></i> Tambah Akun
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <form action="{{ route('userManagement') }}" method="GET">
                        <div class="input-group">
                            <select name="id_role" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Pilih Role untuk Filter --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id_role }}" {{ request('id_role') == $role->id_role ? 'selected' : '' }}>
                                        {{ $role->role }}
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
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Divisi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $u)
                    <tr>
                        <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                        
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->role->role ?? 'N/A' }}</td>
                        <td>{{ $u->division->division_name ?? 'N/A' }}</td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                    data-bs-target="#editUserModal"
                                    data-id="{{ $u->id_user }}"
                                    data-name="{{ $u->name }}"
                                    data-username="{{ $u->username }}"
                                    data-email="{{ $u->email }}"
                                    data-id_role="{{ $u->id_role }}"
                                    data-id_division="{{ $u->id_division }}">
                                Edit
                            </button>

                            <form action="{{ route('users.delete', ['id' => $u->id_user]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE') <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Anda yakin ingin menghapus akun {{ $u->name }}?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            @if(request()->has('id_role') && request('id_role') != '')
                                Tidak ada data pengguna untuk role yang dipilih.
                            @else
                                Silakan pilih role untuk menampilkan data pengguna.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-end">
                @if ($users instanceof \Illuminate\Pagination\AbstractPaginator)
                    {{ $users->links() }}
                @endif
            </div>

        </div>
    </div>
</div>

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
                    <label for="add_name">Nama</label>
                    <input type="text" name="name" id="add_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="add_username">Username</label>
                    <input type="text" name="username" id="add_username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="add_email">Email</label>
                    <input type="email" name="email" id="add_email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="add_role">Role</label>
                    <select name="id_role" id="add_role" class="form-select" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id_role }}">{{ $role->role }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="add_division">Divisi</label>
                    <select name="id_division" id="add_division" class="form-select">
                        <option value="">-- Tidak Ada Divisi --</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id_division }}">{{ $division->division_name }}</option>
                        @endforeach
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
                    <label for="edit_name">Nama</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="edit_username">Username</label>
                    <input type="text" name="username" id="edit_username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="edit_email">Email</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="edit_id_role">Role</label>
                    <select name="id_role" id="edit_id_role" class="form-select" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id_role }}">{{ $role->role }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="edit_id_division">Divisi</label>
                    <select name="id_division" id="edit_id_division" class="form-select">
                        <option value="">-- Tidak Ada Divisi --</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id_division }}">{{ $division->division_name }}</option>
                        @endforeach
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
// Kode JS ini sudah benar, tidak perlu diubah.
document.getElementById('editUserModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    document.getElementById('edit_id').value = button.getAttribute('data-id');
    document.getElementById('edit_name').value = button.getAttribute('data-name');
    document.getElementById('edit_username').value = button.getAttribute('data-username');
    document.getElementById('edit_email').value = button.getAttribute('data-email');
    document.getElementById('edit_id_role').value = button.getAttribute('data-id_role');
    document.getElementById('edit_id_division').value = button.getAttribute('data-id_division');
});
</script>
@endpush
@endsection