@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-primary text-white p-4">
                    <h3 class="mb-0 fw-bold"><i class="bi bi-calendar-plus me-2"></i> Ajukan Rapat Baru</h3>
                    <p class="mb-0 text-white-50">Isi formulir di bawah ini untuk mengajukan jadwal rapat baru.</p>
                </div>
                <div class="card-body p-5">
                    
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong><i class="bi bi-exclamation-triangle-fill me-2"></i> Terdapat Kesalahan:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('pic.meetings.store') }}" method="POST" enctype="multipart/form-data" id="createMeetingForm" novalidate>
                        @csrf

                        <!-- Section 1: Informasi Dasar -->
                        <h5 class="text-primary mb-3 border-bottom pb-2"><i class="bi bi-info-circle me-2"></i> Informasi Dasar</h5>
                        
                        <div class="mb-4">
                            <label for="judul" class="form-label fw-bold">Judul Rapat <span class="text-danger">*</span></label>
                            <input type="text" name="judul" id="judul" class="form-control form-control-lg" placeholder="Contoh: Rapat Koordinasi Bulanan" required value="{{ old('judul') }}">
                            <div class="invalid-feedback">Mohon isi judul rapat.</div>
                        </div>

                        <div class="mb-4">
                            <label for="desc" class="form-label fw-bold">Deskripsi Rapat (Opsional)</label>
                            <textarea name="desc" id="desc" class="form-control" rows="3" placeholder="Tambahkan catatan atau agenda rapat...">{{ old('desc') }}</textarea>
                        </div>

                        <!-- Section 2: Waktu & Tempat -->
                        <h5 class="text-primary mb-3 border-bottom pb-2 mt-5"><i class="bi bi-clock me-2"></i> Waktu & Tempat</h5>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="tanggal" class="form-label fw-bold">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" id="tanggal" class="form-control" required value="{{ old('tanggal') }}">
                                <div class="invalid-feedback">Mohon pilih tanggal.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="waktu_start" class="form-label fw-bold">Waktu Mulai <span class="text-danger">*</span></label>
                                <input type="time" name="waktu_start" id="waktu_start" class="form-control" required value="{{ old('waktu_start') }}">
                                <div class="invalid-feedback">Mohon tentukan waktu mulai.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="waktu_end" class="form-label fw-bold">Waktu Selesai <span class="text-danger">*</span></label>
                                <input type="time" name="waktu_end" id="waktu_end" class="form-control" required value="{{ old('waktu_end') }}">
                                <div class="invalid-feedback">Mohon tentukan waktu selesai.</div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="id_cabang" class="form-label fw-bold">Lokasi Cabang <span class="text-danger">*</span></label>
                                <select name="id_cabang" id="id_cabang" class="form-select form-select-lg" required>
                                    <option value="">-- Pilih Cabang --</option>
                                    @foreach($cabangs as $cabang)
                                        <option value="{{ $cabang->id }}" {{ old('id_cabang') == $cabang->id ? 'selected' : '' }}>
                                            {{ $cabang->cabang }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Mohon pilih cabang.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="id_room" class="form-label fw-bold">Ruangan <span class="text-danger">*</span></label>
                                <select name="id_room" id="id_room" class="form-select form-select-lg" required disabled>
                                    <option value="">-- Pilih Ruangan --</option>
                                </select>
                                <div id="room-help-text" class="form-text text-muted mt-2">
                                    <i class="bi bi-info-circle"></i> Silakan pilih <strong>Cabang</strong>, <strong>Tanggal</strong>, dan <strong>Waktu</strong> terlebih dahulu untuk melihat ketersediaan ruangan.
                                </div>
                                <div class="invalid-feedback">Mohon pilih ruangan yang tersedia.</div>
                            </div>
                        </div>

                        <!-- Section 3: Dokumen Pendukung -->
                        <h5 class="text-primary mb-3 border-bottom pb-2 mt-5"><i class="bi bi-paperclip me-2"></i> Dokumen Pendukung</h5>
                        
                        <div class="row g-3 mb-4">
                            {{-- Kategori 1: Materi --}}
                            <div class="col-md-6">
                                <div class="card h-100 border-primary shadow-sm upload-zone position-relative" data-target="files_materi" style="cursor: pointer; transition: all 0.2s;">
                                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-file-earmark-text me-2"></i>Materi</span>
                                        <span class="badge bg-white text-primary rounded-pill" id="count-files_materi">0</span>
                                    </div>
                                    <div class="card-body p-2 d-flex flex-column">
                                        <ul class="list-group list-group-flush mb-2 flex-grow-1" id="list-files_materi" style="min-height: 50px;">
                                            <li class="list-group-item text-center text-muted small fst-italic py-3">Belum ada file.</li>
                                        </ul>
                                        <div class="p-3 text-center border rounded bg-light dashed-border mt-auto">
                                            <i class="bi bi-cloud-arrow-up text-primary fs-3"></i>
                                            <p class="small mb-0 text-muted">Drag & Drop atau Klik di sini</p>
                                        </div>
                                        <input type="file" class="d-none" id="files_materi" name="files_materi[]" multiple>
                                    </div>
                                </div>
                            </div>

                            {{-- Kategori 2: Notulensi --}}
                            <div class="col-md-6">
                                <div class="card h-100 border-success shadow-sm upload-zone position-relative" data-target="files_notulensi" style="cursor: pointer; transition: all 0.2s;">
                                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-journal-text me-2"></i>Notulensi</span>
                                        <span class="badge bg-white text-success rounded-pill" id="count-files_notulensi">0</span>
                                    </div>
                                    <div class="card-body p-2 d-flex flex-column">
                                        <ul class="list-group list-group-flush mb-2 flex-grow-1" id="list-files_notulensi" style="min-height: 50px;">
                                            <li class="list-group-item text-center text-muted small fst-italic py-3">Belum ada file.</li>
                                        </ul>
                                        <div class="p-3 text-center border rounded bg-light dashed-border mt-auto">
                                            <i class="bi bi-cloud-arrow-up text-success fs-3"></i>
                                            <p class="small mb-0 text-muted">Drag & Drop atau Klik di sini</p>
                                        </div>
                                        <input type="file" class="d-none" id="files_notulensi" name="files_notulensi[]" multiple>
                                    </div>
                                </div>
                            </div>

                            {{-- Kategori 3: Dokumentasi --}}
                            <div class="col-md-6">
                                <div class="card h-100 border-info shadow-sm upload-zone position-relative" data-target="files_dokumentasi" style="cursor: pointer; transition: all 0.2s;">
                                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-camera me-2"></i>Dokumentasi</span>
                                        <span class="badge bg-white text-info rounded-pill" id="count-files_dokumentasi">0</span>
                                    </div>
                                    <div class="card-body p-2 d-flex flex-column">
                                        <ul class="list-group list-group-flush mb-2 flex-grow-1" id="list-files_dokumentasi" style="min-height: 50px;">
                                            <li class="list-group-item text-center text-muted small fst-italic py-3">Belum ada file.</li>
                                        </ul>
                                        <div class="p-3 text-center border rounded bg-light dashed-border mt-auto">
                                            <i class="bi bi-cloud-arrow-up text-info fs-3"></i>
                                            <p class="small mb-0 text-muted">Drag & Drop atau Klik di sini</p>
                                        </div>
                                        <input type="file" class="d-none" id="files_dokumentasi" name="files_dokumentasi[]" multiple>
                                    </div>
                                </div>
                            </div>

                            {{-- Kategori 4: Lainnya --}}
                            <div class="col-md-6">
                                <div class="card h-100 border-secondary shadow-sm upload-zone position-relative" data-target="files_lainnya" style="cursor: pointer; transition: all 0.2s;">
                                    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-paperclip me-2"></i>Lainnya</span>
                                        <span class="badge bg-white text-secondary rounded-pill" id="count-files_lainnya">0</span>
                                    </div>
                                    <div class="card-body p-2 d-flex flex-column">
                                        <ul class="list-group list-group-flush mb-2 flex-grow-1" id="list-files_lainnya" style="min-height: 50px;">
                                            <li class="list-group-item text-center text-muted small fst-italic py-3">Belum ada file.</li>
                                        </ul>
                                        <div class="p-3 text-center border rounded bg-light dashed-border mt-auto">
                                            <i class="bi bi-cloud-arrow-up text-secondary fs-3"></i>
                                            <p class="small mb-0 text-muted">Drag & Drop atau Klik di sini</p>
                                        </div>
                                        <input type="file" class="d-none" id="files_lainnya" name="files_lainnya[]" multiple>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-text text-muted mb-4">
                            Format yang didukung: PDF, DOC, DOCX, PPT, PPTX, JPG, PNG. Maksimal 20MB per file.
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-5">
                            <a href="{{ route('pic.meetings.index') }}" class="btn btn-light btn-lg me-md-2 border">
                                <i class="bi bi-arrow-left me-2"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-send me-2"></i> Ajukan Rapat
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

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
    
    /* Styling for disabled options to make them look distinct but readable */
    select option:disabled {
        color: #adb5bd;
        background-color: #f8f9fa;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('createMeetingForm');
    const cabangSelect = document.getElementById('id_cabang');
    const roomSelect = document.getElementById('id_room');
    const dateInput = document.getElementById('tanggal');
    const startTimeInput = document.getElementById('waktu_start');
    const endTimeInput = document.getElementById('waktu_end');
    const roomHelpText = document.getElementById('room-help-text');
    
    // Fetch all meetings for availability check (passed from controller if possible, or fetch via API)
    // Since we didn't pass 'allRapats' in the create method yet, we might need to fetch them or rely on the room API to return availability.
    // However, the previous implementation in index.blade.php fetched allRapats.
    // Let's assume we will update the controller to pass 'allRapats' or we fetch room availability via a dedicated endpoint.
    // For now, let's use the same logic as index.blade.php but we need the data.
    // I will update the controller to pass 'allRapats' as well.
    
    // Wait, I can't easily update the controller in this step without a separate tool call.
    // But I can use the existing /cabang/{id}/rooms endpoint. 
    // Does that endpoint return availability?
    // Let's check the endpoint logic if I can.
    // But for now, let's implement the client-side logic assuming we can get the data.
    
    // Actually, the best way is to fetch the rooms and then filter them client side if we have the meetings data.
    // Or better, the API should return available rooms.
    // The current API `getRoomsByCabang` just returns rooms.
    
    // I will add `allRapats` to the view in the next step (Controller update).
    // For now, let's write the JS assuming `allRapats` is available globally or I fetch it.
    // To be safe, I'll fetch all meetings via an API if possible, or just inject it.
    // I'll inject it in the controller update.
    
    const allRapats = @json($allRapats ?? []); 

    async function updateRoomOptions() {
        const cabangId = cabangSelect.value;
        
        roomSelect.innerHTML = '<option value="">-- Pilih Ruangan --</option>';
        roomSelect.disabled = true;
        
        if (!cabangId) {
            roomHelpText.innerHTML = '<i class="bi bi-info-circle"></i> Silakan pilih <strong>Cabang</strong> terlebih dahulu.';
            return;
        }

        roomHelpText.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat ruangan...';

        try {
            const url = "{{ route('cabang.rooms', ':id') }}".replace(':id', cabangId);
            const response = await fetch(url);
            if (!response.ok) throw new Error('Gagal mengambil data ruangan');
            
            const rooms = await response.json();

            if (rooms.length === 0) {
                roomHelpText.textContent = 'Tidak ada ruangan tersedia untuk cabang ini.';
                return;
            }

            rooms.forEach(room => {
                const option = new Option(room.room, room.id_room);
                // Store status for availability check
                option.dataset.statusId = room.status_ruangan ? room.status_ruangan.id_status : room.status_ruangan_id;
                option.dataset.roomName = room.room;
                roomSelect.add(option);
            });

            roomSelect.disabled = false;
            checkRoomAvailability();

        } catch (error) {
            console.error('Error fetching rooms:', error);
            roomHelpText.textContent = 'Gagal memuat ruangan. Silakan coba lagi.';
        }
    }

    function checkRoomAvailability() {
        const selectedDate = dateInput.value;
        const selectedStartTime = startTimeInput.value;
        const selectedEndTime = endTimeInput.value;
        
        if (!selectedDate || !selectedStartTime) {
            roomHelpText.innerHTML = '<i class="bi bi-exclamation-circle text-warning"></i> Pilih <strong>Tanggal</strong> & <strong>Waktu Mulai</strong> untuk melihat status ketersediaan.';
            return;
        }

        roomHelpText.innerHTML = '<i class="bi bi-check-circle text-success"></i> Menampilkan status ketersediaan ruangan.';

        const selectedStartDateTime = new Date(`${selectedDate}T${selectedStartTime}`);
        const selectedEndDateTime = selectedEndTime ? new Date(`${selectedDate}T${selectedEndTime}`) : new Date(selectedStartDateTime.getTime() + 60 * 60 * 1000);

        Array.from(roomSelect.options).forEach(option => {
            if (!option.value) return;

            const roomId = option.value;
            const staticStatusId = option.dataset.statusId;
            let isAvailable = true;
            let reason = '';

            // Check static status (e.g. Under Maintenance)
            if (staticStatusId == 2) {
                isAvailable = false;
                reason = ' (Sedang Perbaikan)';
            }

            // Check schedule overlap
            if (isAvailable) {
                for (const rapat of allRapats) {
                    if (rapat.id_room == roomId && rapat.tanggal === selectedDate) {
                        const rapatStart = new Date(`${rapat.tanggal}T${rapat.waktu_start}`);
                        const rapatEnd = rapat.waktu_end ? new Date(`${rapat.tanggal}T${rapat.waktu_end}`) : new Date(rapatStart.getTime() + 60 * 60 * 1000);
                        
                        // Check overlap
                        if (selectedStartDateTime < rapatEnd && selectedEndDateTime > rapatStart) {
                            isAvailable = false;
                            reason = ` (Dipakai: ${rapat.waktu_start.substring(0,5)} - ${rapat.waktu_end.substring(0,5)})`;
                            break;
                        }
                    }
                }
            }

            option.disabled = !isAvailable;
            const statusClass = isAvailable ? 'status-available' : 'status-unavailable';
            const roomName = option.dataset.roomName;
            
            // Re-render option text with status
            // Note: HTML inside option is not supported in all browsers, but we can use unicode or just text
            // For better UI, we might need a custom dropdown, but for now standard select with text indication is safest.
            option.text = `${isAvailable ? '✅' : '❌'} ${roomName}${reason}`;
        });
    }

    // Event Listeners
    cabangSelect.addEventListener('change', updateRoomOptions);
    dateInput.addEventListener('change', checkRoomAvailability);
    startTimeInput.addEventListener('change', checkRoomAvailability);
    endTimeInput.addEventListener('change', checkRoomAvailability);

    // File Upload Preview
    // File Upload Logic
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
                alert(errorMessage);
            }
        }

        function renderList() {
            list.innerHTML = '';
            countBadge.textContent = dataTransfer.files.length;

            if (dataTransfer.files.length === 0) {
                list.innerHTML = '<li class="list-group-item text-center text-muted small fst-italic py-3">Belum ada file.</li>';
                return;
            }

            Array.from(dataTransfer.files).forEach((file, index) => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center small py-1';
                
                const fileInfo = document.createElement('span');
                fileInfo.innerHTML = `<i class="bi bi-file-earmark me-2"></i> ${file.name} <small class="text-muted ms-1">(${Math.round(file.size/1024)} KB)</small>`;

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
    });

    // Form Validation
    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
</script>
@endpush
