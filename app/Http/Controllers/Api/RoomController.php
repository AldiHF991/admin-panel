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

        // --- LOGIKA BARU: PENGECEKAN KETERSEDIAAN YANG DIOPTIMALKAN ---
        // Jika request menyertakan tanggal dan waktu, kita cek ketersediaannya.
        if ($request->has('tanggal') && $request->has('waktu_start')) {
            $tanggal = $request->tanggal;
            $waktuStart = $request->waktu_start;
            // Waktu selesai bersifat opsional, jika tidak ada, anggap rapat berlangsung 1 jam.
            $waktuEnd = $request->waktu_end ?? date('H:i:s', strtotime($waktuStart.' +1 hour'));

            // Ambil semua ID ruangan yang ada
            $roomIds = $rooms->pluck('id_room')->toArray();

            // Ambil SEMUA rapat yang tumpang tindih untuk ruangan-ruangan ini dalam SATU query
            // PERBAIKAN: Ambil detail rapat, bukan hanya ID
            $conflictingMeetings = \App\Models\Rapat::whereIn('id_room', $roomIds)
                ->where('tanggal', $tanggal)
                // Filter status Rapat: Hanya anggap rapat yang AKTIF (misal: Menunggu, Disetujui, Berlangsung).
                // Ditolak (2) dan Selesai (5) TIDAK MENGHALANGI ruangan.
                ->whereNotIn('id_status', [2, 5]) 
                ->where(function ($query) use ($waktuStart, $waktuEnd) {
                    // Skenario 1: Rapat existing memiliki waktu selesai
                    $query->where(function ($q) use ($waktuStart, $waktuEnd) {
                        $q->whereNotNull('waktu_end')
                            ->where('waktu_start', '<', $waktuEnd)
                            ->where('waktu_end', '>', $waktuStart);
                    })
                    // Skenario 2: Rapat existing "tidak menentu" (waktu_end NULL)
                    ->orWhere(function ($q) use ($waktuStart) {
                         $q->whereNull('waktu_end')
                           ->where('waktu_start', '<=', $waktuStart);
                    });
                })
                ->get(['id_room', 'waktu_start', 'waktu_end', 'judul']);

            // Buat mapping id_room => booking info
            $bookingInfo = [];
            foreach ($conflictingMeetings as $meeting) {
                $bookingInfo[$meeting->id_room] = [
                    'waktu_start' => $meeting->waktu_start,
                    'waktu_end' => $meeting->waktu_end,
                    'judul' => $meeting->judul,
                ];
            }

            foreach ($rooms as $room) {
                // Jika ruangan sudah status 2 (Tidak Tersedia/Maintenance) dari database, biarkan saja.
                // Kita hanya ubah jika ruangan aslinya Tersedia (1) TAPI ada booking.
                if ($room->status_ruangan_id == 1 && isset($bookingInfo[$room->id_room])) {
                    $room->status_ruangan_id = 2; // Set jadi Tidak Tersedia untuk sesi ini
                    // Tambahkan info booking
                    $room->booking_info = $bookingInfo[$room->id_room];
                }
            }
        }
        
        // Urutkan ruangan berdasarkan nama agar rapi di dropdown
        $rooms = $rooms->sortBy('room')->values();
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
