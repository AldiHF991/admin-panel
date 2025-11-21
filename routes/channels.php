<?php

use Illuminate\Support\Facades\Broadcast;

// Public channel untuk absensi rapat.
// Tidak memerlukan otorisasi, sehingga bisa diakses oleh layar display.
Broadcast::channel('Absensi.Rapat.{id}', function () {
    return true;
});
