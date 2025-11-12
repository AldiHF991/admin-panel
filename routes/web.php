<?php

use App\Http\Controllers\Website\AdminController;
use App\Http\Controllers\Website\AuthController;
use App\Http\Controllers\Website\RapatController;
use App\Http\Controllers\Api\AbsensiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/absensi/{id}', function ($id) {
    return view('meetings.absensi', ['id' => $id]);
});

Route::get('/rapat/{id_rapat}/absensi', [AbsensiController::class, 'getByRapat']);

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login'); // Anda bisa memberi nama yang sama

Route::get('/admin/dashboard', [AdminController::class, 'showDashboard'])->name('dashboard');
Route::get('/admin/user-management', [AdminController::class, 'showUserManagement'])->name('userManagement');
Route::get('/admin/branch', [AdminController::class, 'showBranch'])->name('branch');
Route::get('/admin/meetings', [AdminController::class, 'showMeetings'])->name('meetings.index');
Route::get('/admin/reports', [AdminController::class, 'showReports'])->name('reports');

// Route for meeting management
Route::get('/meetings/{rapat}/qr', [RapatController::class, 'showQrCode'])->name('meetings.showQr');
Route::get('/meetings/{rapat}/qr-code', [RapatController::class, 'getQrCodeSvg'])->name('meetings.getQrCodeSvg');
Route::resource('/admin/meetings', RapatController::class)->except(['index', 'show', 'create', 'edit']);

Route::post('admin/users/add', [AdminController::class, 'storeUserAccount'])->name('users.add');
Route::post('admin/users/edit', [AdminController::class, 'updateUserAccount'])->name('users.edit');
Route::delete('admin/users/{id}/delete', [AdminController::class, 'deleteUserAccount'])->name('users.delete');

Route::post('admin/branch/add', [AdminController::class, 'storeBranch'])->name('branch.add');
Route::post('admin/room/add', [AdminController::class, 'storeRoom'])->name('room.add');
Route::post('admin/branch/edit', [AdminController::class, 'updateBranch'])->name('branch.edit');
Route::delete('admin/branch/{id}/delete', [AdminController::class, 'deleteBranch'])->name('branch.delete');
Route::post('admin/room/edit', [AdminController::class, 'updateRoom'])->name('room.edit');
Route::delete('admin/room/{id}/delete', [AdminController::class, 'deleteRoom'])->name('room.delete');

Route::get('/cabang/{cabang}/rooms', [RapatController::class, 'getRoomsByCabang'])->name('cabang.rooms');
Route::post('/meetings', [RapatController::class, 'store'])->name('meetings.store');
Route::put('/meetings/{rapat}', [RapatController::class, 'update'])->name('meetings.update'); // <-- TAMBAHKAN ATAU PASTIKAN BARIS INI ADA
Route::delete('/meetings/{rapat}', [RapatController::class, 'destroy'])->name('meetings.destroy');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// routes/web.php - tambahkan di akhir file
Route::get('/api/rapat/{id}/absensi', [App\Http\Controllers\Api\AbsensiController::class, 'getByRapat']);