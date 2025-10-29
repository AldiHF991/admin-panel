@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Manajemen Cabang & Ruang</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Daftar cabang dan ruang rapat yang terdaftar dalam sistem.</p>

            <table class="table table-striped mt-3">
                <thead class="table-success">
                    <tr>
                        <th>#</th>
                        <th>Nama Cabang</th>
                        <th>Nama Ruang</th>
                        <th>Kapasitas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Kantor Pusat</td>
                        <td>Ruang Rapat A</td>
                        <td>30 Orang</td>
                        <td>
                            <button class="btn btn-sm btn-info">Edit</button>
                            <button class="btn btn-sm btn-danger">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
