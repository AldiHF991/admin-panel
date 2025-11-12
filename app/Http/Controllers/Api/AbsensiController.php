<?php

namespace App\Http\Controllers\Api;

use App\Exports\AbsensiExport;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Rapat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class AbsensiController extends Controller
{
    public function store(Request $request)
    {
        // Menonaktifkan endpoint ini untuk memaksa absensi via QR Code.
        // Endpoint yang benar adalah POST /api/rapat/scan-absen
        return response()->json([
            'message' => 'Metode absensi ini tidak digunakan. Silakan gunakan pemindai QR Code.',
        ], 405); // 405 Method Not Allowed
    }

    /**
     * Endpoint untuk melihat riwayat absensi user yang sedang login.
     */
    public function history()
    {
        $user = Auth::user();

        $history = Absensi::where('id_user', $user->id_user)
            ->with('rapat:id_rapat,judul,tanggal') // Mengambil data rapat terkait
            ->orderBy('waktu_absen', 'desc')
            ->get();

        return response()->json(['data' => $history]);
    }

    public function getByRapat($id_rapat)
{
    // Ambil semua data absensi beserta data user
    $data = Absensi::with('user:id_user,name,email')
        ->where('id_rapat', $id_rapat)
        ->orderBy('waktu_absen', 'asc')
        ->get();

    return response()->json([
        'data' => $data
    ]);
}


    public function export()
    {
        return Excel::download(new AbsensiExport, 'absensi.xlsx');
    }
}
