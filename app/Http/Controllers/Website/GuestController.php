<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function showGuest()
    {
        return view('auth.guest');
    }

    /**
     * Menyimpan data tamu dari formulir guest umum (tanpa rapat spesifik).
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:100',
            'asal_instansi' => 'required|string|max:100',
            'jabatan' => 'required|string|max:100',
            'nomor' => 'nullable|string|max:25',
        ]);

        Guest::create($validatedData);

        // Tidak ada pencatatan absensi di sini karena ini adalah buku tamu umum
        return redirect()->route('guest')->with('success', 'Terima kasih, '.$validatedData['nama'].'. Kehadiran Anda telah berhasil dicatat.');
    }
}
