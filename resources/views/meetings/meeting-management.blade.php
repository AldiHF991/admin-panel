@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">Manajemen Rapat</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Lihat dan kelola jadwal rapat. QR Code akan tampil di sini.</p>

            <div class="text-center my-4">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Rapat123"
                     alt="QR Code" class="shadow-sm rounded">
                <p class="mt-2 text-muted">QR Code Rapat (contoh)</p>
            </div>

            <table class="table table-bordered mt-3">
                <thead class="table-warning">
                    <tr>
                        <th>#</th>
                        <th>Judul Rapat</th>
                        <th>Tanggal</th>
                        <th>Cabang</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Evaluasi Mingguan</td>
                        <td>2025-10-29</td>
                        <td>Kantor Pusat</td>
                        <td><span class="badge bg-success">Selesai</span></td>
                        <td><button class="btn btn-sm btn-outline-primary">Detail</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
