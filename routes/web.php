<?php

use App\Http\Controllers\Website\AdminController;
use App\Http\Controllers\Website\AuthController;
use App\Http\Controllers\Website\GuestController;
use App\Http\Controllers\Website\PicController;
use App\Http\Controllers\Website\RapatController;
use App\Http\Middleware\AdminWebsiteMiddleware;
use App\Http\Middleware\PicWebsiteMiddleware;
use App\Http\Middleware\VerifyGuestWebsiteMiddleware;
use App\Models\Rapat;
use Illuminate\Support\Facades\Route;

// Public routes (tidak perlu admin)
Route::get('/', [AuthController::class, 'landingPage'])->name('landingPage');
// Route::get('/', [GuestController::class, 'showGuest'])->name('guest');
// Route::post('/', [GuestController::class, 'store'])->name('guest.store');

Route::get('/test-websocket', function () {
    return view('test-websocket');
});

Route::get('/trigger-test-event', function () {
    \App\Events\TestWebSocket::dispatch('Hello from WebSocket!');
    return 'Event dispatched!';
});

Route::get('/debug/trigger-attendance/{id}', function ($id) {
    // Buat dummy absensi
    $absensi = new \App\Models\Absensi();
    $absensi->id_absensi = \Illuminate\Support\Str::uuid();
    $absensi->id_rapat = $id;
    $absensi->waktu_absen = now();
    $absensi->id_status_kehadiran = 2;
    $absensi->attendable_type = 'App\Models\Guest'; // Simulasi Guest
    $absensi->guest_name = 'Test User ' . rand(1, 100);
    $absensi->guest_jabatan = 'Tester';
    $absensi->guest_instansi = 'Debug Inc';
    
    // Dispatch event
    \App\Events\AttendanceRecorded::dispatch($absensi);
    
    return 'Attendance Event dispatched for Meeting ID: ' . $id;
});

Route::get('/adminpanel/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/adminpanel/login', [AuthController::class, 'login'])->name('login');

Route::get('/meetings/{rapat}/guest', [RapatController::class, 'showGuestLogin'])->name('meetings.guestLogin');
Route::post('/meetings/{rapat}/guest', [RapatController::class, 'storeGuest'])->name('meetings.storeGuest');
// Terapkan middleware 'guest.session' untuk melindungi dashboard tamu.
// Hanya tamu dengan sesi yang valid yang bisa mengakses halaman ini.
Route::get('/meetings/{rapat}/guest/{guest}/dashboard', [RapatController::class, 'showGuestDashboard'])->name('meetings.guestDashboard')->middleware([VerifyGuestWebsiteMiddleware::class]);
Route::get('/meetings/{rapat}/guest/logout', [RapatController::class, 'logoutGuest'])->name('guest.logout');

// Route untuk download file, perlu di luar middleware admin agar semua user terotentikasi bisa akses
Route::middleware('auth')->group(function () {
    Route::get('/cabang/{cabang}/rooms', [RapatController::class, 'getRoomsByCabang'])->name('cabang.rooms');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Notification Routes
    Route::get('/notifications/{id}/read', [AdminController::class, 'markNotificationAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [AdminController::class, 'markAllNotificationsAsRead'])->name('notifications.markAllRead');
});

// Route untuk download file, bisa diakses oleh user auth maupun guest yang valid
Route::get('/meetings/files/{file}', [RapatController::class, 'downloadFile'])->name('meetings.downloadFile');

// Admin routes (perlu admin)
Route::middleware([AdminWebsiteMiddleware::class])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'showDashboard'])->name('dashboard');
    Route::get('/admin/user-management', [AdminController::class, 'showUserManagement'])->name('userManagement');
    Route::get('/admin/branch', [AdminController::class, 'showBranch'])->name('branch');
    Route::get('/admin/meetings', [AdminController::class, 'showMeetings'])->name('meetings.index');
    Route::get('/admin/reports', [AdminController::class, 'showReports'])->name('reports');
    Route::get('/admin/reports/recent-activity', [AdminController::class, 'showRecentActivityReport'])->name('reports.recentActivity');
    Route::get('/admin/reports/newly-created', [AdminController::class, 'showNewlyCreatedReport'])->name('reports.newlyCreated');
    Route::get('/admin/reports/newly-created', [AdminController::class, 'showNewlyCreatedReport'])->name('reports.newlyCreated');

    // Routes untuk RapatController
    Route::get('/dashboard/tables', [RapatController::class, 'dashboardTables'])->name('dashboard.tables');
    Route::get('/meetings/incoming', [RapatController::class, 'incoming'])->name('meetings.incoming');
    Route::get('/meetings/incoming/data', [RapatController::class, 'getIncomingData'])->name('meetings.incoming.data');
    Route::resource('meetings', RapatController::class)->parameters([
        'meetings' => 'rapat'
    ]);
    // Route::put('/meetings/{rapat}', [RapatController::class, 'update'])->name('meetings.update'); // Redundant with resource
    Route::delete('/meetings/{id}', [RapatController::class, 'destroy'])->name('meetings.destroy');
    Route::post('/meetings/{rapat}/accept', [RapatController::class, 'accept'])->name('meetings.accept');
    Route::post('/meetings/{rapat}/reject', [RapatController::class, 'reject'])->name('meetings.reject');
    Route::get('/meetings/{rapat}/qr', [RapatController::class, 'showQrCode'])->name('meetings.showQr');
    Route::get('/meetings/{rapat}/qr-code', [RapatController::class, 'getQrCodeSvg'])->name('meetings.getQrCodeSvg');
    Route::get('/meetings/{rapat}/absensi', [RapatController::class, 'showAbsensi'])->name('meetings.showAbsensi');
    Route::get('/meetings/{id}/export-absensi', [RapatController::class, 'exportAbsensi'])->name('meetings.exportAbsensi');
    Route::delete('/meetings/files/{file}', [RapatController::class, 'destroyFile'])->name('meetings.destroyFile');
    Route::get('/meetings/{id}/files', [RapatController::class, 'getFiles'])->name('meetings.getFiles');
    Route::get('/meetings/{id}/guest-qr', [RapatController::class, 'showGuestQr'])->name('meetings.showGuestQr');

    Route::post('admin/users/add', [AdminController::class, 'storeUserAccount'])->name('users.add');
    Route::post('admin/users/edit', [AdminController::class, 'updateUserAccount'])->name('users.edit');
    Route::delete('admin/users/{id}/delete', [AdminController::class, 'deleteUserAccount'])->name('users.delete');

    Route::post('admin/branch/add', [AdminController::class, 'storeBranch'])->name('branch.add');
    Route::post('admin/room/add', [AdminController::class, 'storeRoom'])->name('room.add');
    Route::post('admin/branch/edit', [AdminController::class, 'updateBranch'])->name('branch.edit');
    Route::delete('admin/branch/{id}/delete', [AdminController::class, 'deleteBranch'])->name('branch.delete');
    Route::post('admin/room/edit', [AdminController::class, 'updateRoom'])->name('room.edit');
    Route::delete('admin/room/{id}/delete', [AdminController::class, 'deleteRoom'])->name('room.delete');


    // Guest Management Routes
    Route::get('/admin/guests', [GuestController::class, 'index'])->name('guests.index');
    Route::delete('/admin/guests/{id}', [GuestController::class, 'destroy'])->name('guests.destroy');

    // Route::get('/display-absensi/{rapatId}', function ($rapatId) {
    //     // Ambil data absensi yang sudah ada untuk rapat ini
    //     $initialAbsensi = \App\Models\Absensi::with('user')
    //         ->where('id_rapat', $rapatId)
    //         ->orderBy('waktu_absen', 'asc')
    //         ->get();

    //     return view('absensi-display', ['rapatId' => $rapatId, 'initialAbsensi' => $initialAbsensi]);
    // })->name('absensi.display');

});

// PIC Routes
Route::middleware(['auth', PicWebsiteMiddleware::class])->group(function () {
    Route::get('/pic/dashboard', [PicController::class, 'dashboard'])->name('pic.dashboard');
    Route::get('/pic/dashboard/tables', [PicController::class, 'dashboardTables'])->name('pic.dashboard.tables');
    Route::get('/pic/meetings', [PicController::class, 'index'])->name('pic.meetings.index');
    Route::get('/pic/meetings/create', [PicController::class, 'create'])->name('pic.meetings.create');
    Route::post('/pic/meetings', [PicController::class, 'store'])->name('pic.meetings.store');
    Route::get('/pic/meetings/{rapat}', [PicController::class, 'show'])->name('pic.meetings.show');
    Route::delete('/pic/meetings/{id}', [PicController::class, 'destroy'])->name('pic.meetings.destroy');
    
    // Additional features requested
    Route::get('/pic/meetings/{rapat}/absensi', [\App\Http\Controllers\Website\PicController::class, 'showAbsensi'])->name('pic.meetings.absensi');
    Route::get('/pic/meetings/{rapat}/qr', [\App\Http\Controllers\Website\PicController::class, 'showQrCode'])->name('pic.meetings.qr');
    Route::get('/pic/meetings/{rapat}/guest-qr', [\App\Http\Controllers\Website\PicController::class, 'showGuestQr'])->name('pic.meetings.guestQr');
    Route::get('/pic/meetings/{rapat}/qr-svg', [\App\Http\Controllers\Website\PicController::class, 'getQrCodeSvg'])->name('pic.meetings.getQrCodeSvg');
    Route::post('/pic/meetings/{rapat}/finish', [\App\Http\Controllers\Website\PicController::class, 'finishMeeting'])->name('pic.meetings.finish');
    
    // Reports
    Route::get('/pic/reports/recent-activity', [\App\Http\Controllers\Website\PicController::class, 'showRecentActivityReport'])->name('pic.reports.recentActivity');
    Route::get('/pic/reports/newly-created', [\App\Http\Controllers\Website\PicController::class, 'showNewlyCreatedReport'])->name('pic.reports.newlyCreated');

    // File Management
    Route::post('/pic/meetings/{rapat}/files', [\App\Http\Controllers\Website\PicController::class, 'storeFile'])->name('pic.meetings.storeFile');
    Route::delete('/pic/meetings/files/{file}', [\App\Http\Controllers\Website\PicController::class, 'destroyFile'])->name('pic.meetings.destroyFile');
});
