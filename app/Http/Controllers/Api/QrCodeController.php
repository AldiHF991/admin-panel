<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Rapat;
use Carbon\Carbon;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    /**
     * Endpoint untuk LAYAR DISPLAY (mengambil QR token)
     */
    public function getQrToken($id)
    {
        $rapat = Rapat::findOrFail($id);

        // Kirim token yang *saat ini* harus ditampilkan
        return response()->json([
            'current_qr_token' => $rapat->current_qr_token,
            'expires_at' => $rapat->qr_token_expires_at->toIso8601String(),
        ]);
    }

    /**
     * Endpoint untuk APLIKASI SCANNER (validasi dan simpan absen)
     */
    public function scanAbsen(Request $request)
    {
        $request->validate([
            'scanned_token' => 'required|string',
        ]);

        $scanned_token = $request->scanned_token;
        $user = $request->user(); // Dapatkan user yang sedang login (via Sanctum)
        $now = Carbon::now();

        // Validasi 3 lapis
        $rapat = Rapat::where(function ($query) use ($scanned_token) {
            $query->where('current_qr_token', $scanned_token)
                ->orWhere('previous_qr_token', $scanned_token);
        })
            ->where('qr_token_expires_at', '>', $now)
            ->first();

        // Jika rapat tidak ditemukan (token salah atau kadaluwarsa)
        if (! $rapat) {
            return response()->json([
                'message' => 'QR Code tidak valid atau sudah kadaluwarsa.',
            ], 422);
        }

        // --- Token VALID! ---
        // Cek apakah user sudah absen
        $sudahAbsen = Absensi::where('id_rapat', $rapat->id_rapat)
            ->where('id_user', $user->id_user)
            ->exists();

        if ($sudahAbsen) {
            return response()->json([
                'message' => 'Anda sudah tercatat hadir di rapat ini.',
            ], 409);
        }

        // Catat absensi baru
        Absensi::create([
            'id_rapat' => $rapat->id_rapat,
            'id_user' => $user->id_user,
            'waktu_absen' => $now,
        ]);

        return response()->json([
            'message' => 'Absensi berhasil! Selamat datang di rapat: '.$rapat->judul,
            'rapat' => $rapat->judul,
        ]);
    }
}
