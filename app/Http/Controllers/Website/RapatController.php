<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Rapat;
use App\Models\Cabang;
use App\Models\User;
use Illuminate\Http\Request;

class RapatController extends Controller
{
    public function index()
    {
        $pics = User::where('id_role', 2)->get(); // Assuming PIC is a role

        return view('meetings.meeting-management', compact('pics'));
    }

    public function getMeetingsByPic(User $user)
    {
        // Menggunakan kolom 'id_user_pengaju' dan relasi 'status' yang benar
        $rapats = Rapat::with(['cabang', 'room', 'status'])
            ->where('id_user_pengaju', $user->id_user)
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json($rapats);
    }

    public function showQrCode(Rapat $rapat)
    {
        return view('meetings.qr-code', compact('rapat'));
    }

    public function store(Request $request)
    {
        // Validasi data yang masuk
        $validatedData = $request->validate([
            'id_cabang' => 'nullable|exists:cabang,id',
            'id_room' => 'nullable|exists:room,id_room',
            'judul' => 'required|string|max:100',
            'tanggal' => 'required|date',
            'waktu_start' => 'required',
            'waktu_end' => 'nullable|after:waktu_start',
            'id_user_pengaju' => 'required|exists:users,id_user',
        ]);

        // Set status default ke 'Diterima' karena dibuat oleh Admin
        $validatedData['id_status'] = 1;

        Rapat::create($validatedData);

        // Redirect kembali ke halaman manajemen dengan query string PIC yang sama
        $redirectUrl = route('meetings.index');
        if ($request->id_user_pengaju) {
            $redirectUrl .= '?id_user_pic=' . $request->id_user_pengaju;
        }
        return redirect($redirectUrl)->with('success', 'Rapat berhasil ditambahkan.');
    }

    public function update(Request $request, Rapat $rapat)
    {
        // Validasi data yang masuk
        $validatedData = $request->validate([
            'id_cabang' => 'nullable|exists:cabang,id',
            'id_room' => 'nullable|exists:room,id_room',
            'judul' => 'required|string|max:100',
            'tanggal' => 'required|date',
            'waktu_start' => 'required',
            'waktu_end' => 'nullable|after:waktu_start',
            'desc' => 'nullable|string|max:100',
            'id_status' => 'required|exists:status_rapat,id_status',
        ]);

        $rapat->update($validatedData);

        // Redirect kembali ke halaman manajemen dengan query string PIC yang sama
        $redirectUrl = route('meetings.index');
        if ($rapat->id_user_pengaju) {
            $redirectUrl .= '?id_user_pic=' . $rapat->id_user_pengaju;
        }
        return redirect($redirectUrl)->with('success', 'Rapat berhasil diperbarui.');
    }

    public function destroy(Rapat $rapat)
    {
        $rapat->delete();

        return redirect()->route('meetings.index')->with('success', 'Rapat berhasil dihapus.');
    }

    public function getRoomsByCabang(Cabang $cabang)
{
    try {
        // Mengambil ruangan yang berelasi dengan $cabang
        $rooms = $cabang->room()
            ->with('statusRuangan')
            ->get();

        return response()->json($rooms, 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Gagal mengambil data ruangan.', 
            'error' => $e->getMessage()
        ], 500);
    }
}
}
