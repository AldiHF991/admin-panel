@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

<div class="row g-4 mb-4">
    <!-- Card: Total Rapat -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="bg-primary text-white p-3 rounded me-3">
                    <i class="bi bi-calendar3 fs-3"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0">Total Rapat</h5>
                    <h3 class="fw-bold text-primary mt-1">24</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Card: Total Pengguna -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="bg-success text-white p-3 rounded me-3">
                    <i class="bi bi-people-fill fs-3"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0">Total Pengguna</h5>
                    <h3 class="fw-bold text-success mt-1">18</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Card: Total Cabang -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="bg-warning text-white p-3 rounded me-3">
                    <i class="bi bi-building fs-3"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0">Total Cabang</h5>
                    <h3 class="fw-bold text-warning mt-1">6</h3>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
