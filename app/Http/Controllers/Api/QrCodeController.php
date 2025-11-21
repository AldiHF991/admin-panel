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
            'id_status_kehadiran'=> 2, // Hadir
        ]);

        return response()->json([
            'message' => 'Absensi berhasil! Selamat datang di rapat: '.$rapat->judul,
            'rapat' => $rapat->judul,
        ]);
    }

    /**
     * Endpoint untuk TAMU (validasi token dan simpan absen tamu).
     * POST: /api/rapat/guest-scan-absen
     */
    public function guestScanAbsen(Request $request)
    {
        // 1. Validasi input dari formulir tamu
        $validatedData = $request->validate([
            'scanned_token' => 'required|string',
            'guest_name' => 'required|string|max:255',
            'guest_jabatan' => 'required|string|max:255',
            'guest_instansi' => 'required|string|max:255',
        ]);

        $scanned_token = $validatedData['scanned_token'];
        $now = Carbon::now();

        // 2. Validasi token QR (sama seperti di scanAbsen)
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
        // 3. Catat absensi tamu
        Absensi::create([
            'id_rapat' => $rapat->id_rapat,
            'id_user' => null, // id_user dikosongkan untuk tamu
            'guest_name' => $validatedData['guest_name'],
            'guest_jabatan' => $validatedData['guest_jabatan'],
            'guest_instansi' => $validatedData['guest_instansi'],
            'waktu_absen' => $now,
            'id_status_kehadiran'=> 2, // Hadir
        ]);

        return response()->json([
            'message' => 'Absensi berhasil! Selamat datang di rapat: '.$rapat->judul,
            'rapat' => $rapat->judul,
        ], 201); // 201 Created
    }
}
