@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ajukan Rapat Baru</h3>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('pic.meetings.store') }}" method="POST">
                        @csrf
                        
                        <div class="form-group mb-3">
                            <label for="judul">Judul Rapat</label>
                            <input type="text" name="judul" id="judul" class="form-control" required value="{{ old('judul') }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="id_cabang">Cabang</label>
                            <select name="id_cabang" id="id_cabang" class="form-control" required>
                                <option value="">Pilih Cabang</option>
                                @foreach($cabangs as $cabang)
                                    <option value="{{ $cabang->id }}" {{ old('id_cabang') == $cabang->id ? 'selected' : '' }}>
                                        {{ $cabang->cabang }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="id_room">Ruangan</label>
                            <select name="id_room" id="id_room" class="form-control" required>
                                <option value="">Pilih Ruangan</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id_room }}" {{ old('id_room') == $room->id_room ? 'selected' : '' }}>
                                        {{ $room->room_name }} (Kapasitas: {{ $room->capacity }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="tanggal">Tanggal</label>
                            <input type="date" name="tanggal" id="tanggal" class="form-control" required value="{{ old('tanggal') }}">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="waktu_start">Waktu Mulai</label>
                                    <input type="time" name="waktu_start" id="waktu_start" class="form-control" required value="{{ old('waktu_start') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="waktu_end">Waktu Selesai</label>
                                    <input type="time" name="waktu_end" id="waktu_end" class="form-control" required value="{{ old('waktu_end') }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="desc">Deskripsi</label>
                            <textarea name="desc" id="desc" class="form-control" rows="3">{{ old('desc') }}</textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Ajukan Rapat</button>
                            <a href="{{ route('pic.meetings.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
