@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detail Rapat</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 200px">Judul</th>
                            <td>{{ $rapat->judul }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge {{ $rapat->id_status == 1 ? 'bg-success' : ($rapat->id_status == 2 ? 'bg-danger' : 'bg-warning') }}">
                                    {{ $rapat->status->status_rapat ?? '-' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Cabang</th>
                            <td>{{ $rapat->cabang->cabang ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Ruangan</th>
                            <td>{{ $rapat->room->room ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td>{{ $rapat->tanggal }}</td>
                        </tr>
                        <tr>
                            <th>Waktu</th>
                            <td>{{ $rapat->waktu_start }} - {{ $rapat->waktu_end }}</td>
                        </tr>
                        <tr>
                            <th>Deskripsi</th>
                            <td>{{ $rapat->desc ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Pengaju</th>
                            <td>{{ $rapat->pengaju->name ?? '-' }}</td>
                        </tr>
                    </table>

                    <div class="mt-3">
                        <a href="{{ route('pic.meetings.index') }}" class="btn btn-secondary">Kembali</a>
                        <a href="{{ route('pic.meetings.absensi', $rapat->id_rapat) }}" class="btn btn-info">Lihat Absensi</a>
                        <a href="{{ route('pic.meetings.qr', $rapat->id_rapat) }}" class="btn btn-dark">Lihat QR Code</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
