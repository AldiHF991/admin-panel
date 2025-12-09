@extends('layouts.app')

@section('title', 'Manajemen Akun')
@section('page-title', 'Manajemen Akun')

@section('content')
{{-- Elemen untuk Loading Overlay --}}
<div id="loading-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1060; justify-content: center; align-items: center;">
    <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>
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
                       <form action="{{ route('userManagement') }}" method="GET" id="filter-form">
                <div class="row mb-3 g-2">
                    <div class="col-md-6">
                        <select name="id_role" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Pilih Role untuk Filter --</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id_role }}" {{ request('id_role') == $role->id_role ? 'selected' : '' }}>
                                    {{ $role->role }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="search" id="search-input" class="form-control" placeholder="Cari berdasarkan nama..." value="{{ request('search') }}">
                    </div>
                </div>
            </form>

            <div class="table-responsive">
            <table class="table table-hover mt-3">
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>
                            @php
                                $nextNameDirection = (request('sort') === 'name' && request('direction') === 'asc') ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ route('userManagement', array_merge(request()->query(), ['sort' => 'name', 'direction' => $nextNameDirection])) }}" class="text-decoration-none text-black">
                                Nama
                                @if (request('sort') === 'name')
                                    <i class="bi {{ request('direction') === 'asc' ? 'bi-sort-alpha-down' : 'bi-sort-alpha-up' }}"></i>
                                @else
                                    <i class="bi bi-sort-alpha-down-alt text-muted"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            @php
                                $nextEmailDirection = (request('sort') === 'email' && request('direction') === 'asc') ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ route('userManagement', array_merge(request()->query(), ['sort' => 'email', 'direction' => $nextEmailDirection])) }}" class="text-decoration-none text-black">
                                Email
                                @if (request('sort') === 'email')
                                    <i class="bi {{ request('direction') === 'asc' ? 'bi-sort-alpha-down' : 'bi-sort-alpha-up' }}"></i>
                                @else
                                    <i class="bi bi-sort-alpha-down-alt text-muted"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            @php
                                $nextRoleDirection = (request('sort') === 'role' && request('direction') === 'asc') ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ route('userManagement', array_merge(request()->query(), ['sort' => 'role', 'direction' => $nextRoleDirection])) }}" class="text-decoration-none text-black">
                                Role
                                @if (request('sort') === 'role')
                                    <i class="bi {{ request('direction') === 'asc' ? 'bi-sort-alpha-down' : 'bi-sort-alpha-up' }}"></i>
                                @else
                                    <i class="bi bi-sort-alpha-down-alt text-muted"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            @php
                                $nextDivDirection = (request('sort') === 'division' && request('direction') === 'asc') ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ route('userManagement', array_merge(request()->query(), ['sort' => 'division', 'direction' => $nextDivDirection])) }}" class="text-decoration-none text-black">
                                Divisi
                                @if (request('sort') === 'division')
                                    <i class="bi {{ request('direction') === 'asc' ? 'bi-sort-alpha-down' : 'bi-sort-alpha-up' }}"></i>
                                @else
                                    <i class="bi bi-sort-alpha-down-alt text-muted"></i>
                                @endif
                            </a>
                        </th>
                        <th>Device Name</th>
                        <th>Device ID</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $u)
                    <tr id="user-row-{{ $u->id_user }}">
                        <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                        
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->role->role ?? 'N/A' }}</td>
                        <td>{{ $u->division->division_name ?? 'N/A' }}</td>
                        <td class="device-name-cell">
                            @if($u->activeDevice)
                                <span class="badge bg-success">{{ $u->activeDevice->manufacturer }} {{ $u->activeDevice->model }}</span>
                            @elseif($u->latestDevice)
                                <span class="badge bg-secondary">{{ $u->latestDevice->manufacturer }} {{ $u->latestDevice->model }} (Tidak Aktif)</span>
                            @elseif($u->device_name)
                                <span class="badge bg-success">{{ $u->device_name }}</span>
                            @else
                                <span class="text-muted">Tidak terhubung</span>
                            @endif
                        </td>
                        <td class="device-id-cell">
                            @if($u->activeDevice)
                                <small class="text-muted" title="{{ $u->activeDevice->device_fingerprint }}">{{ Str::limit($u->activeDevice->device_fingerprint, 20) }}</small>
                            @elseif($u->latestDevice)
                                <small class="text-muted" title="{{ $u->latestDevice->device_fingerprint }}">{{ Str::limit($u->latestDevice->device_fingerprint, 20) }}</small>
                            @elseif($u->device_id)
                                <small class="text-muted" title="{{ $u->device_id }}">{{ Str::limit($u->device_id, 20) }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
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

                            @php
                                $device = $u->activeDevice ?? $u->latestDevice;
                            @endphp

                            @if($u->device_id || $device)
                                <button class="btn btn-sm btn-outline-danger reset-device-btn" 
                                        data-user-id="{{ $u->id_user }}"
                                        data-user-name="{{ $u->name }}"
                                        data-device-name="{{ $u->device_name ?? ($device ? $device->model : '-') }}"
                                        data-device-id="{{ $u->device_id ?? ($device ? $device->device_fingerprint : '-') }}"
                                        data-manufacturer="{{ $device ? $device->manufacturer : '-' }}"
                                        data-os-version="{{ $device ? $device->os_version : '-' }}"
                                        data-app-version="{{ $device ? $device->build_id : '-' }}"
                                        title="Reset Device">
                                    <i class="bi bi-phone-x"></i> Reset
                                </button>
                            @endif

                            <form action="{{ route('users.delete', ['id' => $u->id_user]) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE') <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Anda yakin ingin menghapus akun {{ $u->name }}?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            @if(request()->has('id_role') && request('id_role') != '')
                                Tidak ada pengguna yang cocok dengan filter dan pencarian.
                            @elseif(request()->has('search') && request('search') != '')
                                Tidak ada pengguna yang cocok dengan pencarian.
                            @else
                                Belum ada data pengguna.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>

            {{-- Pagination tidak diubah --}}
            <div class="d-flex justify-content-end">
                @if ($users instanceof \Illuminate\Pagination\AbstractPaginator)
                    {{ $users->appends(request()->query())->links('pagination::simple-bootstrap-5') }}
                @endif
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('users.add') }}" method="POST" class="modal-content" id="addUserForm">
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
                                 <label for="add_name">Nama <span class="text-danger">*</span></label>
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
                        <div class="input-group">
                            <input type="password" name="password" id="add_password" class="form-control @error('password') is-invalid @enderror" required>
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password="add_password">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="add_password_confirmation">Konfirmasi Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="add_password_confirmation" class="form-control" required>
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password="add_password_confirmation">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                            <div class="invalid-feedback" id="add_password_match_error"></div>
                        </div>
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
                <button type="submit" id="addUserSubmitBtn" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('users.edit') }}" method="POST" class="modal-content" id="editUserForm">
            @csrf
            <input type="hidden" name="id_user" id="edit_id">
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
                        <div class="input-group">
                            <input type="password" name="password" id="edit_password" class="form-control @error('password') is-invalid @enderror">
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password="edit_password">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_password_confirmation">Konfirmasi Password</label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="edit_password_confirmation" class="form-control">
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password="edit_password_confirmation">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                            <div class="invalid-feedback" id="edit_password_match_error"></div>
                        </div>
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
                <button type="submit" id="editUserSubmitBtn" class="btn btn-warning">Update</button>
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

// SKRIP BARU: Validasi real-time untuk konfirmasi password
document.addEventListener('DOMContentLoaded', function() {
    // --- Ambil elemen tombol submit ---
    const addUserSubmitBtn = document.getElementById('addUserSubmitBtn');
    const editUserSubmitBtn = document.getElementById('editUserSubmitBtn');

    // Fungsi untuk validasi password
    function validatePasswordConfirmation(passwordInput, confirmInput, errorElement, submitButton) {
        const password = passwordInput.value;
        const confirmPassword = confirmInput.value;

        // Untuk form edit, jika kedua password kosong, tidak ada error.
        if (submitButton.id === 'editUserSubmitBtn' && !password && !confirmPassword) {
            errorElement.style.display = 'none';
            confirmInput.classList.remove('is-invalid');
            submitButton.disabled = false;
            return;
        }

        if (password !== confirmPassword) {
            errorElement.textContent = 'Konfirmasi password tidak cocok dengan password.';
            errorElement.style.display = 'block';
            confirmInput.classList.add('is-invalid');
            submitButton.disabled = true; // Nonaktifkan tombol jika password tidak cocok
        } else {
            errorElement.style.display = 'none';
            confirmInput.classList.remove('is-invalid');
            submitButton.disabled = false; // Aktifkan kembali tombol jika password cocok
        }
    }

    // --- Modal Tambah Akun ---
    const addPassword = document.getElementById('add_password');
    const addConfirmPassword = document.getElementById('add_password_confirmation');
    const addError = document.getElementById('add_password_match_error');

    addPassword.addEventListener('input', () => validatePasswordConfirmation(addPassword, addConfirmPassword, addError, addUserSubmitBtn));
    addConfirmPassword.addEventListener('input', () => validatePasswordConfirmation(addPassword, addConfirmPassword, addError, addUserSubmitBtn));


    // --- Modal Edit Akun ---
    const editPassword = document.getElementById('edit_password');
    const editConfirmPassword = document.getElementById('edit_password_confirmation');
    const editError = document.getElementById('edit_password_match_error');

    editPassword.addEventListener('input', () => validatePasswordConfirmation(editPassword, editConfirmPassword, editError, editUserSubmitBtn));
    editConfirmPassword.addEventListener('input', () => validatePasswordConfirmation(editPassword, editConfirmPassword, editError, editUserSubmitBtn));

    // SKRIP BARU: Toggle show/hide password
    document.querySelectorAll('[data-toggle-password]').forEach(button => {
        button.addEventListener('click', function () {
            const targetId = this.getAttribute('data-toggle-password');
            const passwordInput = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        });
    });

    // SKRIP BARU: Live search dengan debounce
    const searchInput = document.getElementById('search-input');
    const filterForm = document.getElementById('filter-form');
    let debounceTimer;

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            // Periksa apakah form ada sebelum submit
            if (filterForm) {
                filterForm.submit();
            }
        }, 500); // Tunggu 500ms setelah pengguna berhenti mengetik
    });

    // SKRIP BARU: Menampilkan loading screen saat submit
    const loadingOverlay = document.getElementById('loading-overlay');

    // Untuk form tambah
    const addForm = document.getElementById('addUserForm');
    if(addForm) {
        addForm.addEventListener('submit', function() {
            loadingOverlay.style.display = 'flex';
        });
    }

    // Untuk form edit
    const editForm = document.getElementById('editUserForm');
    if(editForm) {
        editForm.addEventListener('submit', function() {
            loadingOverlay.style.display = 'flex';
        });
    }

    // Untuk semua form hapus
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function() {
            loadingOverlay.style.display = 'flex';
        });
    });

    // SKRIP BARU: Reset Device Modal Handler
    const resetDeviceModalEl = document.getElementById('resetDeviceModal');
    let resetModal;
    if (resetDeviceModalEl) {
        resetModal = new bootstrap.Modal(resetDeviceModalEl);
    }
    
    let currentResetUserId = null;

    // 1. Handle tombol Reset di tabel -> Buka Modal
    document.addEventListener('click', function(e) {
        if (e.target.closest('.reset-device-btn')) {
            const btn = e.target.closest('.reset-device-btn');
            currentResetUserId = btn.getAttribute('data-user-id');
            const userName = btn.getAttribute('data-user-name');
            const deviceName = btn.getAttribute('data-device-name');
            const deviceId = btn.getAttribute('data-device-id');
            const manufacturer = btn.getAttribute('data-manufacturer');
            const osVersion = btn.getAttribute('data-os-version');
            const appVersion = btn.getAttribute('data-app-version');
            
            // Isi data ke modal
            document.getElementById('modal-user-name').textContent = userName;
            document.getElementById('modal-device-name').textContent = deviceName;
            document.getElementById('modal-device-id').textContent = deviceId;

            // Updated fields
            const mfgEl = document.getElementById('modal-manufacturer');
            if(mfgEl) mfgEl.textContent = manufacturer;
            
            const osEl = document.getElementById('modal-os-version');
            if(osEl) osEl.textContent = osVersion;
            
            const appEl = document.getElementById('modal-app-version');
            if(appEl) appEl.textContent = appVersion;
            
            // Buka modal
            if (resetModal) resetModal.show();
        }
    });

    // 2. Handle tombol Konfirmasi di Modal -> Eksekusi Reset
    const confirmResetBtn = document.getElementById('confirmResetBtn');
    if (confirmResetBtn) {
        confirmResetBtn.addEventListener('click', function() {
            if (!currentResetUserId) return;
            
            const btn = this;
            const originalText = btn.innerHTML;
            
            // Set loading state
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            
            fetch(`/admin/users/${currentResetUserId}/reset-device`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                // Reset button state
                btn.disabled = false;
                btn.innerHTML = originalText;
                
                // Tutup modal
                if (resetModal) resetModal.hide();
                
                if (data.success) {
                    // Update the table row dynamically
                    const row = document.getElementById(`user-row-${currentResetUserId}`);
                    if (row) {
                        // Update device name cell
                        const deviceNameCell = row.querySelector('.device-name-cell');
                        deviceNameCell.innerHTML = '<span class="text-muted">Tidak terhubung</span>';
                        
                        // Update device ID cell
                        const deviceIdCell = row.querySelector('.device-id-cell');
                        deviceIdCell.innerHTML = '<span class="text-muted">-</span>';
                        
                        // Remove the reset button
                        const resetBtn = row.querySelector('.reset-device-btn');
                        if (resetBtn) resetBtn.remove();
                    }
                    
                    alert('Berhasil! ' + data.message);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                if (resetModal) resetModal.hide();
                alert('Terjadi kesalahan: ' + error.message);
            });
        });
    }

});

</script>
@endpush
@endsection

{{-- MODAL RESET DEVICE DEVICE --}}
<div class="modal fade" id="resetDeviceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Reset Device Link</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div class="mb-4">
                    <span class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-phone text-danger" style="font-size: 2.5rem;"></i>
                    </span>
                </div>
                
                <h4 class="mb-2 fw-bold text-dark">Konfirmasi Reset</h4>
                <p class="text-muted mb-4">Anda akan melepas keterkaitan perangkat untuk akun ini.</p>
                
                <div class="card border-0 bg-light text-start mb-4">
                    <div class="card-body p-3">
                        <div class="row mb-2">
                            <div class="col-4 text-muted small">User</div>
                            <div class="col-8 fw-bold text-dark" id="modal-user-name">-</div>
                        </div>
                        <h6 class="text-muted small mt-3 mb-2 text-uppercase fw-bold">Device Information</h6>
                        <hr class="mt-1 mb-2">
                        <div class="row mb-1">
                            <div class="col-4 text-muted small">Manufacturer</div>
                            <div class="col-8 text-dark small" id="modal-manufacturer">-</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-4 text-muted small">Model</div>
                            <div class="col-8 text-dark small" id="modal-device-name">-</div>
                        </div>
                         <div class="row mb-1">
                            <div class="col-4 text-muted small">OS Version</div>
                            <div class="col-8 text-dark small" id="modal-os-version">-</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-4 text-muted small">App Version</div>
                            <div class="col-8 text-dark small" id="modal-app-version">-</div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-4 text-muted small">Device ID</div>
                            <div class="col-8 font-monospace small text-dark text-break" id="modal-device-id">-</div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning d-flex align-items-center mb-0 p-2 small" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <div>User dapat login dari perangkat baru setelah ini.</div>
                </div>
            </div>
            <div class="modal-footer justify-content-center border-0 pb-4">
                <button type="button" class="btn btn-light px-4 me-2" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger px-4" id="confirmResetBtn">
                    Ya, Reset Device
                </button>
            </div>
        </div>
    </div>
</div>
