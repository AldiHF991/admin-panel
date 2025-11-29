<?php

use App\Http\Controllers\Api\AbsensiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CabangController;
use App\Http\Controllers\Api\GuestController;
use App\Http\Controllers\Api\QrCodeController;
use App\Http\Controllers\Api\RapatController;
use App\Http\Controllers\Api\RoomController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/userPIC', [AuthController::class, 'getUsersWithRole']);
    Route::get('/pic/get-division', [AuthController::class, 'getDivision']);
    // Route::get('/users-by-division', [AuthController::class, 'getUsersByDivision']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::get('rapat/{id}/export-absensi', [RapatController::class, 'exportAbsensi']); // Endpoint untuk ekspor absensi
    Route::get('/rapat/saya', [RapatController::class, 'rapatSaya']);
    Route::apiResource('rapat', RapatController::class);
    Route::get('rapat/{id}/peserta', [RapatController::class, 'getPesertaRapat']);
    Route::post('rapat/{id}/peserta', [RapatController::class, 'addPesertaRapat']);
    Route::get('rapat/{id}/absensi', [RapatController::class, 'getAbsensiRapat']); // Endpoint untuk melihat daftar absensi dari rapat

    Route::post('rapat/{id}/setujui', [RapatController::class, 'setujuiRapat']);
    Route::post('rapat/{id}/tolak', [RapatController::class, 'tolakRapat']);

    Route::post('/absensi', [AbsensiController::class, 'store']); // Untuk melakukan absensi
    Route::get('/absensi/history', [AbsensiController::class, 'history']); // Untuk melihat riwayat

    // mengubah parameter {id} menjadi {cabang} agar Route Model Binding berfungsi.
    Route::get('/cabang/{cabang}/room', [RoomController::class, 'getRoomsByCabang']);
    Route::get('/cabang', [CabangController::class, 'index']);
    Route::get('/room', [RoomController::class, 'index']);

    // Endpoint untuk LAYAR DISPLAY
    Route::get('/rapat/{id}/qr-token', [QrCodeController::class, 'getQrToken']);
    // Endpoint untuk APLIKASI SCANNER
    Route::post('/rapat/scan-absen', [QrCodeController::class, 'scanAbsen']);

    Route::get('/absensi/export/', [AbsensiController::class, 'export']);

});

// Endpoint untuk form tamu (Guest Mode)
// Route::apiResource('/form-guest', [GuestController::class]);

// Endpoint untuk tamu (tidak memerlukan otentikasi login)
Route::post('/rapat/guest-scan-absen', [QrCodeController::class, 'guestScanAbsen']);

// Route untuk mengambil data rapat berdasarkan PIC untuk web admin
Route::get('/meetings/by-pic/{user}', [\App\Http\Controllers\Website\RapatController::class, 'getMeetingsByPic']);

// // Tambahkan :id_cabang setelah {cabang}
// Route::get('/cabang/{cabang:id}/room', [RoomController::class, 'getRoomsByCabang']);
