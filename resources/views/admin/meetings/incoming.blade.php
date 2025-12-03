@extends('layouts.app')

@section('title', 'Permintaan Rapat Masuk')
@section('page-title', 'Permintaan Rapat Masuk')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-inbox-fill me-2"></i>Daftar Permintaan Rapat</h5>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Judul Rapat</th>
                        <th>Waktu</th>
                        <th>Ruangan</th>
                        <th>Pengaju</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($meetings as $meeting)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $meeting->judul }}</div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($meeting->tanggal)->isoFormat('dddd, D MMMM Y') }}</small>
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($meeting->waktu_start)->format('H:i') }} - 
                                {{ \Carbon\Carbon::parse($meeting->waktu_end)->format('H:i') }} WIB
                            </td>
                            <td>{{ $meeting->room->room ?? '-' }}</td>
                            <td>{{ $meeting->pengaju->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-warning text-dark">Menunggu</span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <form action="{{ route('meetings.accept', $meeting->id_rapat) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Apakah Anda yakin ingin menerima permintaan ini?')">
                                            <i class="bi bi-check-lg me-1"></i> Terima
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="openRejectionModal('{{ route('meetings.reject', $meeting->id_rapat) }}')">
                                        <i class="bi bi-x-lg me-1"></i> Tolak
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Belum ada permintaan rapat baru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tolak Rapat (Reused from layout if available, otherwise defined here) -->
<!-- Assuming layout has rejectionModal, but defining it here just in case or relying on layout's modal -->
<!-- Based on previous edits, layout has #rejectionModal. We just need to ensure the script to open it works. -->
@endsection

@push('scripts')
<script type="module">
    console.log('Incoming meetings script loaded');
    
    // Listen for dashboard updates
    window.Echo.private('dashboard')
        .listen('.dashboard.update', (e) => {
            console.log('Dashboard update received:', e);
            refreshIncomingTable();
        });

    function refreshIncomingTable() {
        axios.get('{{ route("meetings.incoming.data") }}')
            .then(response => {
                const tbody = document.querySelector('table tbody');
                if (tbody) {
                    tbody.innerHTML = response.data.html;
                    
                    // Re-initialize any plugins or event listeners if necessary
                    // For example, if you have tooltips or other interactive elements inside the table
                }
            })
            .catch(error => {
                console.error('Error refreshing table:', error);
            });
    }
</script>
@endpush
