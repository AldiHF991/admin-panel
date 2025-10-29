<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Rapat;
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
        // Validation and store logic here
        return redirect()->route('meetings.index')->with('success', 'Rapat berhasil ditambahkan.');
    }

    public function update(Request $request, Rapat $rapat)
    {
        // Validation and update logic here
        return redirect()->route('meetings.index')->with('success', 'Rapat berhasil diperbarui.');
    }

    public function destroy(Rapat $rapat)
    {
        $rapat->delete();

        return redirect()->route('meetings.index')->with('success', 'Rapat berhasil dihapus.');
    }
}
