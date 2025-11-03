@extends('layouts.app')

@section('content')
<div class="container mt-4">

    {{-- Session alerts tidak diubah --}}
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
            {{-- Bagian filter tidak diubah --}}
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

            {{-- Tabel tidak diubah --}}
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

            {{-- Pagination tidak diubah --}}
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
            {{-- Input tersembunyi untuk identifikasi form saat validasi gagal --}}
            <input type="hidden" name="form_type" value="add">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah Akun</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                {{-- Blok untuk menampilkan SEMUA error validasi --}}
                @if ($errors->any() && old('form_type') == 'add')
                    <div class="alert alert-danger" role="alert">
                        <strong>Perhatian!</strong> Terdapat kesalahan pada input Anda:
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-3">
                    {{-- Tambahkan span.text-danger untuk tanda * --}}
                    <label for="add_name">Nama <span class="text-danger">*</span></label>
                    {{-- Tambahkan class @error dan div.invalid-feedback --}}
                    <input type="text" name="name" id="add_name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="add_username">Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" id="add_username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="add_email">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="add_email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="add_password">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="add_password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="add_password_confirmation">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" id="add_password_confirmation" class="form-control" required>
                        {{-- Error untuk konfirmasi biasanya ditangani oleh 'password' rule 'confirmed' --}}
                    </div>
                </div>
                <div class="mb-3">
                    <label for="add_role">Role <span class="text-danger">*</span></label>
                    <select name="id_role" id="add_role" class="form-select @error('id_role') is-invalid @enderror" required>
                        {{-- Hapus <option> default agar 'required' berfungsi penuh --}}
                        @foreach($roles as $role)
                            <option value="{{ $role->id_role }}" {{ old('id_role') == $role->id_role ? 'selected' : '' }}>{{ $role->role }}</option>
                        @endforeach
                    </select>
                    @error('id_role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
              <div class="mb-3">
    {{-- TAMBAHKAN TANDA BINTANG DI LABEL --}}
    <label for="add_division">Divisi <span class="text-danger">*</span></label>
    
    {{-- TAMBAHKAN 'required' PADA SELECT --}}
    <select name="id_division" id="add_division" class="form-select @error('id_division') is-invalid @enderror" required>
        
        {{-- GANTI OPSI DEFAULT INI AGAR KOSONG & DISABLED --}}
        <option value="" disabled selected>-- Pilih Divisi --</option>
        
        @foreach($divisions as $division)
            <option value="{{ $division->id_division }}" {{ old('id_division') == $division->id_division ? 'selected' : '' }}>{{ $division->division_name }}</option>
        @endforeach
    </select>
    
    {{-- Pesan error ini sudah benar --}}
    @error('id_division')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
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
            <input type="hidden" name="form_type" value="edit">

            <div class="modal-header bg-warning">
                <h5 class="modal-title">Edit Akun</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                @if ($errors->any() && old('form_type') == 'edit')
                    <div class="alert alert-danger" role="alert">
                        <strong>Perhatian!</strong> Terdapat kesalahan pada input Anda:
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                {{-- Saya juga tambahkan validasi di modal Edit agar konsisten --}}
                <div class="mb-3">
                    <label for="edit_name">Nama <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="edit_name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="edit_username">Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" id="edit_username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required>
                     @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="edit_email">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="edit_email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                     @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_password">Password Baru</label>
                        <input type="password" name="password" id="edit_password" class="form-control @error('password') is-invalid @enderror">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_password_confirmation">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" id="edit_password_confirmation" class="form-control">
                    </div>
                </div>
                <small class="form-text text-muted mb-3 d-block">Kosongkan password jika tidak ingin mengubahnya.</small>
                <div class="mb-3">
                    <label for="edit_id_role">Role <span class="text-danger">*</span></label>
                    <select name="id_role" id="edit_id_role" class="form-select @error('id_role') is-invalid @enderror" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id_role }}">{{ $role->role }}</option>
                        @endforeach
                    </select>
                     @error('id_role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="edit_id_division">Divisi</label>
                    <select name="id_division" id="edit_id_division" class="form-select @error('id_division') is-invalid @enderror">
                        <option value="">-- Tidak Ada Divisi --</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id_division }}">{{ $division->division_name }}</option>
                        @endforeach
                    </select>
                     @error('id_division')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
    const modal = this;

    // Ambil nilai dari old() jika validasi gagal, jika tidak, ambil dari data-atttribute
    const oldName = @json(old('name'));
    const oldUsername = @json(old('username'));
    const oldEmail = @json(old('email'));
    const oldRole = @json(old('id_role'));
    const oldDivision = @json(old('id_division'));
    const formType = @json(old('form_type'));

    // Hanya isi dari data-attribute jika form_type bukan 'edit' (artinya tidak ada error validasi edit)
    if (formType !== 'edit') {
        modal.querySelector('#edit_id').value = button.getAttribute('data-id');
        modal.querySelector('#edit_name').value = button.getAttribute('data-name');
        modal.querySelector('#edit_username').value = button.getAttribute('data-username');
        modal.querySelector('#edit_email').value = button.getAttribute('data-email');
        modal.querySelector('#edit_id_role').value = button.getAttribute('data-id_role');
        modal.querySelector('#edit_id_division').value = button.getAttribute('data-id_division');
    } else {
        // Jika ADA error validasi 'edit', isi dengan data 'old()'
        // dan ambil ID dari data-attribute karena ID tidak ada di old()
        modal.querySelector('#edit_id').value = button.getAttribute('data-id') || @json(old('id'));
        modal.querySelector('#edit_name').value = oldName;
        modal.querySelector('#edit_username').value = oldUsername;
        modal.querySelector('#edit_email').value = oldEmail;
        modal.querySelector('#edit_id_role').value = oldRole;
        modal.querySelector('#edit_id_division').value = oldDivision;
    }
});

// SKRIP BARU: Untuk membuka kembali modal jika ada error validasi
@if ($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        @if (old('form_type') == 'add')
            var addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
            addUserModal.show();
        @elseif (old('form_type') == 'edit')
            var editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
            editUserModal.show();
        @endif
    });
@endif
</script>
@endpush
@endsection
