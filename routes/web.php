<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Redirect root ke dashboard (cek login)
Route::get('/', function () {
    if (!session('is_logged_in')) {
        return redirect('/login');
    }
    return redirect('/dashboard');
});

// ===== LOGIN SYSTEM STATIC =====

// Halaman Login
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Proses Login
Route::post('/login', function (Request $request) {
    $username = $request->input('username');
    $password = $request->input('password');

    // Login sederhana: admin / 12345
    if ($username === 'admin' && $password === '12345') {
        session(['is_logged_in' => true]);
        return redirect('/dashboard');
    }

    return back()->with('error', 'Username atau password salah!');
})->name('login.process');

// Logout (gunakan POST agar sesuai dengan form di app.blade.php)
Route::post('/logout', function () {
    session()->forget('is_logged_in');
    return redirect('/login');
})->name('logout');

// ===== DASHBOARD DAN MENU =====

// Dashboard
Route::get('/dashboard', function () {
    if (!session('is_logged_in')) {
        return redirect('/login');
    }
    return view('dashboard');
})->name('dashboard');

// Account Management
Route::get('/users', function () {
    if (!session('is_logged_in')) {
        return redirect('/login');
    }
    return view('users.user-management');
})->name('users.management');

// Cabang dan Ruang
Route::get('/branches', function () {
    if (!session('is_logged_in')) {
        return redirect('/login');
    }
    return view('branches.branch-room');
})->name('branches.room');

// Manajemen Rapat
Route::get('/meetings', function () {
    if (!session('is_logged_in')) {
        return redirect('/login');
    }
    return view('meetings.meeting-management');
})->name('meetings.management');

// Laporan Absensi
Route::get('/reports', function () {
    if (!session('is_logged_in')) {
        return redirect('/login');
    }
    return view('reports.report-absensi');
})->name('reports.absensi');

// Tambah, Edit, dan Hapus (sederhana dengan session)
Route::post('/users/add', function (Request $request) {
    $users = session('users', [
        ['id' => 1, 'nama' => 'Admin Utama', 'email' => 'admin@example.com', 'role' => 'Super Admin'],
        ['id' => 2, 'nama' => 'PIC Cabang', 'email' => 'pic@example.com', 'role' => 'PIC'],
    ]);

    $newId = count($users) + 1;
    $users[] = [
        'id' => $newId,
        'nama' => $request->input('nama'),
        'email' => $request->input('email'),
        'role' => $request->input('role'),
    ];

    session(['users' => $users]);
    return redirect()->back();
})->name('users.add');

Route::post('/users/delete', function (Request $request) {
    $users = session('users', []);
    $users = array_filter($users, fn($u) => $u['id'] != $request->input('id'));
    session(['users' => array_values($users)]);
    return redirect()->back();
})->name('users.delete');

Route::post('/users/edit', function (Request $request) {
    $users = session('users', []);
    foreach ($users as &$u) {
        if ($u['id'] == $request->input('id')) {
            $u['nama'] = $request->input('nama');
            $u['email'] = $request->input('email');
            $u['role'] = $request->input('role');
        }
    }
    session(['users' => $users]);
    return redirect()->back();
})->name('users.edit');
