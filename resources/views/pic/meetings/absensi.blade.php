@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Data Absensi Rapat: {{ $rapat->judul }}</h3>
                    <div>
                        <a href="{{ route('meetings.exportAbsensi', $rapat->id_rapat) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                        <a href="{{ route('pic.meetings.show', $rapat->id_rapat) }}" class="btn btn-secondary btn-sm">Kembali</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Peserta</th>
                                    <th>Instansi/Divisi</th>
                                    <th>Waktu Absen</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($absensi as $index => $absen)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $absen->user->name ?? $absen->guest_name ?? 'Tamu' }}</td>
                                        <td>{{ $absen->user->division->division_name ?? $absen->guest_instansi ?? '-' }}</td>
                                        <td>{{ $absen->waktu_absen }}</td>
                                        <td>
                                            <span class="badge bg-success">Hadir</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Belum ada data absensi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
