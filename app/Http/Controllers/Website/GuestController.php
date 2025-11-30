<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index(Request $request)
    {               
        $query = Guest::with(['absensi' => function ($query) {
            $query->latest('waktu_absen');
        }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('asal_instansi', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sort = $request->get('sort', 'nama');
        $direction = $request->get('direction', 'asc');
        
        // Handle sorting by related columns if needed, but for now basic sorting
        if (in_array($sort, ['nama', 'asal_instansi', 'jabatan'])) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $guests = $query->paginate(10);

        return view('guest.index', compact('guests', 'sort', 'direction'));
    }

    public function destroy($id)
    {
        try {
            $guest = Guest::findOrFail($id);
            // Absensi will be deleted via database cascade if configured, 
            // otherwise we might need $guest->absensi()->delete();
            // Assuming standard setup or manual cleanup:
            $guest->absensi()->delete();
            $guest->delete();

            return redirect()->route('guest.index')->with('success', 'Data tamu berhasil direset (dihapus).');
        } catch (\Exception $e) {
            return redirect()->route('guest.index')->with('error', 'Gagal menghapus data tamu: ' . $e->getMessage());
        }
    }
}
