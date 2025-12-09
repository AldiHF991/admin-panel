<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Rapat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Events\AttendanceRecorded;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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
     * Endpoint untuk validasi QR token dan membuat session token.
     * Ini dipanggil SEGERA setelah QR di-scan, sebelum foto diambil.
     */
    public function validateQrToken(Request $request)
    {
        $request->validate([
            'scanned_token' => 'required|string',
        ]);

        $scanned_token = $request->scanned_token;
        $user = $request->user();
        $now = Carbon::now();

        // Validasi QR token (sama seperti di scanAbsen)
        $rapat = Rapat::where(function ($query) use ($scanned_token) {
            $query->where('current_qr_token', $scanned_token)
                ->orWhere('previous_qr_token', $scanned_token);
        })
            ->where('qr_token_expires_at', '>', $now)
            ->first();

        if (! $rapat) {
            return response()->json([
                'message' => 'QR Code tidak valid atau sudah kadaluwarsa.',
            ], 422);
        }

        // Cek apakah user sudah absen
        $sudahAbsen = Absensi::where('id_rapat', $rapat->id_rapat)
            ->where('attendable_id', $user->id_user)
            ->where('attendable_type', \App\Models\User::class)
            ->exists();

        if ($sudahAbsen) {
            return response()->json([
                'message' => 'Anda sudah tercatat hadir di rapat ini.',
            ], 409);
        }

        // Buat session token yang valid selama 5 menit
        $sessionToken = Str::uuid()->toString();
        $cacheKey = "qr_session:{$user->id_user}:{$sessionToken}";
        
        // Simpan data rapat di cache
        Cache::put($cacheKey, [
            'id_rapat' => $rapat->id_rapat,
            'user_id' => $user->id_user,
            'validated_at' => $now->toIso8601String(),
        ], now()->addMinutes(5));

        return response()->json([
            'message' => 'QR Code valid. Silakan ambil foto wajah Anda.',
            'session_token' => $sessionToken,
            'rapat' => [
                'id_rapat' => $rapat->id_rapat,
                'judul' => $rapat->judul,
            ],
            'expires_in_seconds' => 300, // 5 menit
        ]);
    }

    /**
     * Endpoint untuk APLIKASI SCANNER (validasi dan simpan absen)
     */
    public function scanAbsen(Request $request)
    {
        $request->validate([
            'scanned_token' => 'nullable|string',
            'session_token' => 'nullable|string',
            'face_photo' => 'nullable|file|image|max:5120', // Validasi foto (opsional, max 5MB)
        ]);

        // Harus ada salah satu: scanned_token atau session_token
        if (!$request->scanned_token && !$request->session_token) {
            return response()->json([
                'message' => 'Diperlukan scanned_token atau session_token.',
            ], 422);
        }

        $user = $request->user(); // Dapatkan user yang sedang login (via Sanctum)
        $now = Carbon::now();
        $rapat = null;

        // Cek apakah menggunakan session_token atau scanned_token
        if ($request->session_token) {
            // Validasi menggunakan session token
            $cacheKey = "qr_session:{$user->id_user}:{$request->session_token}";
            $sessionData = Cache::get($cacheKey);

            if (!$sessionData) {
                return response()->json([
                    'message' => 'Session token tidak valid atau sudah kadaluwarsa. Silakan scan QR code lagi.',
                ], 422);
            }

            // Ambil data rapat dari session
            $rapat = Rapat::find($sessionData['id_rapat']);
            
            if (!$rapat) {
                return response()->json([
                    'message' => 'Rapat tidak ditemukan.',
                ], 404);
            }

            // Hapus session token setelah digunakan
            Cache::forget($cacheKey);
        } else {
            // Validasi menggunakan scanned_token (backward compatibility)
            $scanned_token = $request->scanned_token;
            
            $rapat = Rapat::where(function ($query) use ($scanned_token) {
                $query->where('current_qr_token', $scanned_token)
                    ->orWhere('previous_qr_token', $scanned_token);
            })
                ->where('qr_token_expires_at', '>', $now)
                ->first();

            if (! $rapat) {
                return response()->json([
                    'message' => 'QR Code tidak valid atau sudah kadaluwarsa.',
                ], 422);
            }
        }

        // --- Token VALID! ---
        // Cek apakah user sudah absen
        $sudahAbsen = Absensi::where('id_rapat', $rapat->id_rapat)
            ->where('attendable_id', $user->id_user)
            ->where('attendable_type', \App\Models\User::class)
            ->exists();

        if ($sudahAbsen) {
            return response()->json([
                'message' => 'Anda sudah tercatat hadir di rapat ini.',
            ], 409);
        }

        // Handle File Upload
        $fotoPath = null;
        if ($request->hasFile('face_photo')) {
            $file = $request->file('face_photo');
            // Simpan di storage/app/public/absensi_photos
            $fotoPath = $file->store('absensi_photos', 'public'); 
        }

        // Catat absensi baru
        $absensi = Absensi::create([
            'id_rapat' => $rapat->id_rapat,
            'attendable_id' => $user->id_user,
            'attendable_type' => \App\Models\User::class,
            'waktu_absen' => $now,
            'id_status_kehadiran' => 2, // Hadir
            'face_photo' => $fotoPath, // PERBAIKAN: Gunakan 'face_photo' bukan 'foto_wajah'
        ]);

        AttendanceRecorded::dispatch($absensi);

        return response()->json([
            'message' => 'Absensi berhasil! Selamat datang di rapat: '.$rapat->judul,
            'rapat' => $rapat->judul,
            'id_rapat' => $rapat->id_rapat,
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
        $absensi = Absensi::create([
            'id_rapat' => $rapat->id_rapat,
            'id_user' => null, // id_user dikosongkan untuk tamu
            'guest_name' => $validatedData['guest_name'],
            'guest_jabatan' => $validatedData['guest_jabatan'],
            'guest_instansi' => $validatedData['guest_instansi'],
            'waktu_absen' => $now,
            'id_status_kehadiran' => 2, // Hadir
        ]);

        AttendanceRecorded::dispatch($absensi);

        return response()->json([
            'message' => 'Absensi berhasil! Selamat datang di rapat: '.$rapat->judul,
            'rapat' => $rapat->judul,
        ], 201); // 201 Created
    }
}
