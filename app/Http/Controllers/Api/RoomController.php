<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Memuat relasi statusRuangan untuk memberikan informasi nama status
        $query = Room::query();

        if ($request->has('cabang_id')) {
            $query->where('id_cabang', $request->cabang_id);
        }

        $rooms = $query->get();

        // --- LOGIKA BARU: PENGECEKAN KETERSEDIAAN BERDASARKAN WAKTU ---
        // Jika request menyertakan tanggal dan waktu, kita cek ketersediaannya.
        if ($request->has('tanggal') && $request->has('waktu_start')) {
            $tanggal = $request->tanggal;
            $waktuStart = $request->waktu_start;
            // Waktu selesai bersifat opsional, jika tidak ada, anggap rapat berlangsung 1 jam.
            $waktuEnd = $request->waktu_end ?? date('H:i:s', strtotime($waktuStart.' +1 hour'));

            foreach ($rooms as $room) {
                // Cek apakah ada rapat yang tumpang tindih di ruangan ini
                $isBooked = \App\Models\Rapat::where('id_room', $room->id_room)
                    ->where('tanggal', $tanggal)
                    // Logika overlap: (start1 < end2) and (end1 > start2)
                    // PERBAIKAN: Menambahkan logika untuk menangani rapat "selesai tidak menentu"
                    ->where(function ($query) use ($waktuStart, $waktuEnd) {
                        // Skenario 1: Rapat yang ada memiliki waktu selesai (waktu_end tidak null)
                        // Cek tumpang tindih waktu secara normal.
                        $query->where(function ($q) use ($waktuStart, $waktuEnd) {
                            $q->whereNotNull('waktu_end')
                                ->where('waktu_start', '<', $waktuEnd)
                                ->where('waktu_end', '>', $waktuStart);
                        })
                        // Skenario 2: Rapat yang ada bersifat "tidak menentu" (waktu_end adalah null)
                        // Ruangan dianggap terpakai jika waktu mulai rapat baru >= waktu mulai rapat tidak menentu.
                            ->orWhere(function ($q) use ($waktuStart) {
                                $q->whereNull('waktu_end')
                                    ->where('waktu_start', '<=', $waktuStart);
                            });
                    })
                    ->exists(); // Cukup cek apakah ada atau tidak

                // Set status_ruangan_id secara dinamis
                // 2 = Tidak Tersedia, 1 = Tersedia
                $room->status_ruangan_id = $isBooked ? 2 : 1;
            }
        }
        // -----------------------------------------------------------

        return response()->json($rooms, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getRoomsByCabang(Cabang $cabang)
    {
        try {
            // Mengambil ruangan yang berelasi dengan $cabang
            $rooms = $cabang->room()
                ->with('statusRuangan') // BENAR: Muat relasi 'statusRuangan' dari model Room.
                ->get();

            return response()->json($rooms, 200);
        } catch (\Exception $e) {
            // Tangani jika terjadi error
            return response()->json(['message' => 'Gagal mengambil data ruangan.', 'error' => $e->getMessage()], 500);
        }
    }
}
