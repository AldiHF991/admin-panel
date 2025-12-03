<?php

use Illuminate\Support\Facades\Broadcast;

// Public channel untuk absensi rapat.
// Tidak memerlukan otorisasi, sehingga bisa diakses oleh layar display.
Broadcast::channel('Absensi.Rapat.{id}', function () {
    return true;
});

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id_user === (int) $id;
});

Broadcast::channel('dashboard', function ($user) {
    return true; // Allow all authenticated users
});
