@extends('layouts.app')

@section('title', 'Manajemen Tamu')
@section('page-title', 'Manajemen Tamu')

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
            <h5 class="mb-0">Guest Management</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('guests.index') }}" method="GET" id="filter-form">
                <div class="row mb-3 g-2">
                    <div class="col-md-12">
                        <input type="text" name="search" id="search-input" class="form-control" placeholder="Cari berdasarkan nama, instansi, atau jabatan..." value="{{ request('search') }}">
                    </div>
                </div>
            </form>

            <table class="table table-hover mt-3">
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>
                            @php
                                $nextDirection = (request('sort') === 'nama' && request('direction') === 'asc') ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ route('guests.index', array_merge(request()->query(), ['sort' => 'nama', 'direction' => $nextDirection])) }}" class="text-decoration-none text-black">
                                Nama
                                @if (request('sort') === 'nama')
                                    <i class="bi {{ request('direction') === 'asc' ? 'bi-sort-alpha-down' : 'bi-sort-alpha-up' }}"></i>
                                @else
                                    <i class="bi bi-sort-alpha-down"></i>
                                @endif
                            </a>
                        </th>
                        <th>Jabatan</th>
                        <th>Asal Instansi</th>
                        <th>Nomor</th>
                        <th>Device Token</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($guests as $guest)
                    <tr>
                        <td>{{ $loop->iteration + ($guests->currentPage() - 1) * $guests->perPage() }}</td>
                        <td>{{ $guest->nama }}</td>
                        <td>{{ $guest->jabatan }}</td>
                        <td>{{ $guest->asal_instansi }}</td>
                        <td>{{ $guest->nomor ?? '-' }}</td>
                        <td>
                            @if($guest->absensi->isNotEmpty() && $guest->absensi->first()->device_token)
                                <span class="badge bg-info text-dark" title="{{ $guest->absensi->first()->device_token }}">
                                    {{ Str::limit($guest->absensi->first()->device_token, 20) }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $guest->created_at ? $guest->created_at->format('d/m/Y H:i') : '-' }}</td>
                        <td>
                            <form action="{{ route('guests.destroy', $guest->id_guest) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin mereset (menghapus) data tamu ini? Tamu harus mendaftar ulang jika ingin login kembali.')">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            @if(request()->has('search') && request('search') != '')
                                Tidak ada tamu yang cocok dengan pencarian.
                            @else
                                Belum ada data tamu.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-end">
                @if ($guests instanceof \Illuminate\Pagination\AbstractPaginator)
                    {{ $guests->appends(request()->query())->links('pagination::simple-bootstrap-5') }}
                @endif
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    // Live search dengan debounce
    const searchInput = document.getElementById('search-input');
    const filterForm = document.getElementById('filter-form');
    let debounceTimer;

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            if (filterForm) {
                filterForm.submit();
            }
        }, 500);
    });
</script>
@endpush
@endsection
