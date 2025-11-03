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
            {{-- PERBAIKAN: Tombol dinonaktifkan jika belum ada PIC yang dipilih --}}
            <button class="btn btn-light btn-sm" 
                    data-bs-toggle="modal" 
                    data-bs-target="#addRapatModal"
                    @if(!request('id_user_pic')) 
                        disabled 
                        title="Pilih PIC terlebih dahulu untuk menambah rapat" 
                    @endif>
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
                            <td>{{ $rapat->cabang ? $rapat->cabang->cabang : 'N/A' }}</td>
                            <td>{{ $rapat->room ? $rapat->room->room : 'N/A' }}</td>
                            <td>{{ \Carbon\Carbon::parse($rapat->tanggal)->translatedFormat(    'd/m/Y') }}</td>
                            <td>{{ substr($rapat->waktu_start, 0, 5) }} - {{ $rapat->waktu_end ? substr($rapat->waktu_end, 0, 5) : 'Selesai tidak menentu' }}</td>
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
        <form action="{{ route('meetings.store') }}" method="POST" class="modal-content" id="addRapatForm" novalidate>
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah Rapat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="addRapatBody">
                {{-- URUTAN FIELD DIPERBAIKI --}}
                <div class="mb-3">
                    <label for="add_judul" class="form-label">Judul Rapat</label>
                    <input type="text" class="form-control" id="add_judul" name="judul" required>
                    <div class="invalid-feedback">Judul rapat tidak boleh kosong.</div>
                </div>
                <div class="mb-3">
                    <label for="add_id_user_pengaju" class="form-label">PIC</label>
                    <select class="form-select" id="add_id_user_pengaju" name="id_user_pengaju" required> {{-- ID disesuaikan --}}
                        <option value="">-- Pilih PIC --</option>
                        @foreach($pics as $pic)
                            <option value="{{ $pic->id_user }}">{{ $pic->name }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">Silakan pilih PIC.</div>
                </div>
                <div class="mb-3">
                    <label for="add_tanggal" class="form-label">Tanggal</label>
                    {{-- Mengembalikan ke input date asli --}}
                    <input type="date" class="form-control date-input" id="add_tanggal" name="tanggal" required>
                    <div class="invalid-feedback">Tanggal tidak boleh kosong.</div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="add_waktu_start" class="form-label">Waktu Mulai</label>
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
        <form action="" method="POST" class="modal-content" id="editRapatForm" novalidate>
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

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
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
</style>
@endpush

@push('scripts')
{{-- JS Flatpickr dihapus --}}
<script>

  document.addEventListener('DOMContentLoaded', function () {
    const allRapats = @json($allRapats);
    const editModalEl = document.getElementById('editRapatModal');
    const addModalEl = document.getElementById('addRapatModal');

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
                option.dataset.statusId = room.status_ruangan ? room.status_ruangan.id_status : room.status_ruangan_id;
                option.dataset.roomName = room.room;
                roomSelect.add(option);
            });

            // Set ke nilai awal jika ada (untuk modal edit)
            if (originalRoomId) {
                roomSelect.value = originalRoomId;
            }
            
            // Cek apakah tanggal dan waktu sudah diisi
            const modal = roomSelect.closest('.modal');
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
    editModalEl.addEventListener('show.bs.modal', function (event) {
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
    });

    // Event listener untuk modal tambah
    addModalEl.addEventListener('show.bs.modal', function() {
        const form = addModalEl.querySelector('form');
        form.reset();
        
        const roomSelect = addModalEl.querySelector('.room-select');
        roomSelect.innerHTML = '<option value="">-- Pilih Ruangan --</option>';
        roomSelect.disabled = true;
        
        addModalEl.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        addModalEl.querySelector('.room-help-text').style.display = 'block';
    });

    // Clear error saat user mengetik
    document.querySelectorAll('#addRapatModal input, #addRapatModal select, #editRapatModal input, #editRapatModal select').forEach(input => {
        const eventType = input.tagName === 'SELECT' ? 'change' : 'input';
        input.addEventListener(eventType, (e) => {
            clearError(e.target);
        });
    });
});
</script>
@endpush
