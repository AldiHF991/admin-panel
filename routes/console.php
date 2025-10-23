<?php

use App\Console\Commands\UpdateRapatQrTokens;
use App\Console\Commands\UpdateRapatStatus;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daftarkan jadwal di sini

// Menjalankan update status rapat setiap menit.
Schedule::command(UpdateRapatStatus::class)->everyMinute();

// Menjalankan update token QR setiap 30 detik.
// Trik ini menjalankan command setiap menit, lalu di dalam prosesnya,
// command dijalankan 2 kali dengan jeda 30 detik.
Schedule::command(UpdateRapatQrTokens::class)->everyMinute()->runInBackground()->before(function () {
    // Jalankan untuk detik ke-0
})->after(function () {
    // Tunggu 30 detik, lalu jalankan lagi untuk detik ke-30
    sleep(30);
    Artisan::call('rapat:update-qr');
});
