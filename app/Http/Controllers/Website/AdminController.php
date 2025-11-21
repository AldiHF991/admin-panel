<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Division;
use App\Models\Rapat;
use App\Models\Role;
use App\Models\StatusRapat;
use App\Models\Room;
use App\Models\StatusRuangan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{

    // Show FUNCTIONS START
  
  public function showDashboard()
    {
        // Mengambil data statistik
        $totalRapat = Rapat::count();
        $totalPengguna = User::count();
        $totalCabang = Cabang::count();

        // Mengambil 5 rapat yang akan datang (berdasarkan tanggal)
        $rapatAkanDatang = Rapat::with(['pengaju', 'status', 'room'])
            ->where('tanggal', '>=', now()->toDateString())
            ->orderBy('tanggal', 'asc')
            ->orderBy('waktu_start', 'asc')
            ->take(5)
            ->get();

        // Mengirim data ke view
        return view('dashboard', compact(
            'totalRapat',
            'totalPengguna',
            'totalCabang',
            'rapatAkanDatang'
        ));
    }

    public function showUserManagement(Request $request)
    {
        // 1. Ambil semua role untuk dropdown filter
        $roles = Role::all();
        $divisions = Division::all();

        // 2. Ambil id_role yang dipilih dari request
        $selectedRoleId = $request->input('id_role');

        // 3. Siapkan query user
        $usersQuery = User::with(['role', 'division']);

        // 4. Filter user jika role dipilih, jika tidak, kembalikan koleksi kosong
        $users = $selectedRoleId
            ? $usersQuery->where('id_role', $selectedRoleId)->paginate(10)->appends($request->except('page'))
            : collect();

        return view('users.user-management', compact('users', 'roles', 'divisions'));
    }

    public function showBranch(Request $request)
    {
        // 1. Ambil semua role untuk dropdown filter
        $statusRuangan = StatusRuangan::all();
        $cabang = Cabang::all();

        // 2. Ambil id_role yang dipilih dari request
        $selectedCabangId = $request->input('id_cabang');

        // 3. Siapkan query user
        $roomQuery = Room::with(['cabang', 'statusRuangan']);

        // 4. Filter user jika role dipilih, jika tidak, kembalikan koleksi kosong
        $rooms = $selectedCabangId
            ? $roomQuery->where('id_cabang', $selectedCabangId)->paginate(10)->appends($request->except('page'))
            : collect();

        return view('branches.branch-room', compact('cabang', 'rooms', 'statusRuangan'));
    }

    public function showMeetings(Request $request)
    {
        $cabangs = Cabang::all();
        $rooms = Room::all();
        $divisions = Division::where('id_division', '!=', 2000)->get();
        $statuses = StatusRapat::all();
        $allRapats = Rapat::all(); // Data semua rapat untuk pengecekan di frontend

        $pics = User::where('id_role', 2)->get(); 
        $selectedPicId = $request->input('id_user_pic');

        $rapatsQuery = Rapat::with(['cabang', 'room', 'status', 'pengaju', 'divisions'])->orderBy('tanggal', 'desc');

        // Filter rapat jika PIC dipilih, jika tidak, kembalikan koleksi kosong
        $rapats = $selectedPicId
            ? $rapatsQuery->where('id_user_pengaju', $selectedPicId)->get()
            : collect();

        return view('meetings.meeting-management', compact('rapats', 'pics', 'cabangs', 'rooms', 'divisions', 'statuses', 'allRapats'));
    }

    // END Show FUNCTIONS

    // ACCOUNT MANAGEMENT FUNCTIONS START

    // Store User Account
    // BUG, NEED FOR FIXED
    public function storeUserAccount(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'id_role' => 'required|exists:role,id_role',
            'id_division' => 'nullable|exists:division,id_division',
        ]);

        // Tambahkan password default
        $validated['password'] = Hash::make('password');

        User::create($validated);

        return redirect()->back()->with('success', 'User berhasil ditambahkan!');
    }

    // Update User Account
    public function updateUserAccount(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:users,id_user',
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,'.$request->id.',id_user',
            'email' => 'required|string|email|max:255|unique:users,email,'.$request->id.',id_user',
            'id_role' => 'required|exists:role,id_role',
            'id_division' => 'nullable|exists:division,id_division',
        ]);

        $user = User::find($request->id);
        // Hapus 'id' dari data yang akan diupdate
        unset($validated['id']);
        $user->update($validated);

        return redirect()->back()->with('success', 'User berhasil diupdate!');
    }

    // Delete User Account
    public function deleteUserAccount($id)
    {
        User::find($id)->delete();

        return redirect()->back()->with('success', 'User berhasil dihapus!');
    }

    // END ACCOUNT MANAGEMENT FUNCTIONS

    // CABANG & RUANG MANAGEMENT START

    public function storeBranch(Request $request)
    {
        $validated = $request->validate([
            'cabang' => 'required|string|max:255|unique:cabang,cabang',
            'alamat' => 'required|string|max:255',
        ]);

        Cabang::create($validated);

        return redirect()->back()->with('success', 'Cabang berhasil ditambahkan!');
    }

    public function storeRoom(Request $request)
    {
        $validated = $request->validate([
            'room' => 'required|string|max:255',
            'id_cabang' => 'required|exists:cabang,id',
        ]);

        // Set status default ke 'Tersedia' (asumsi id = 1)
        $validated['status_ruang_id'] = 1;

        Room::create($validated);

        return redirect()->route('branch', ['id_cabang' => $request->id_cabang])->with('success', 'Ruangan berhasil ditambahkan!');
    }

    public function updateBranch(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:cabang,id',
            'cabang' => 'required|string|max:255|unique:cabang,cabang,'.$request->id.',id',
            'alamat' => 'required|string|max:255',
        ]);

        $cabang = Cabang::find($request->id);
        $cabang->update($validated);

        return redirect()->back()->with('success', 'Cabang berhasil diupdate!');
    }

    public function deleteBranch($id)
    {
        $cabang = Cabang::with('room')->findOrFail($id);

        // Hapus semua ruangan yang berelasi dengan cabang ini
        foreach ($cabang->room as $room) {
            // Kita bisa menambahkan pengecekan lain di sini jika diperlukan,
            // misalnya apakah ruangan sedang dibooking.
            // Untuk saat ini, kita langsung hapus.
            $room->delete();
        }

        // Setelah semua ruangan terhapus, hapus cabangnya
        $cabang->delete();

        return redirect()->route('branch')->with('success', 'Cabang berhasil dihapus!');
    }

    public function updateRoom(Request $request)
    {
        $validated = $request->validate([
            'id_room' => 'required|exists:room,id_room',
            'room' => 'required|string|max:255',
            'id_cabang' => 'required|exists:cabang,id',
            'status_ruangan_id' => 'required|exists:status_ruangan,id',
        ]);

        $room = Room::find($request->id_room);
        $room->update($validated);

        return redirect()->back()->with('success', 'Ruangan berhasil diupdate!');
    }

    public function deleteRoom($id)
    {
        try {
            $room = Room::findOrFail($id);
            $room->delete();

            return redirect()->back()->with('success', 'Ruangan berhasil dihapus!');
        } catch (\Illuminate\Database\QueryException $e) {
            // Check for foreign key constraint violation
            if ($e->getCode() == '23000') {
                return redirect()->back()->with('error', 'Gagal menghapus! Ruangan ini sedang digunakan dalam data rapat.');
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus ruangan.');
        }
    }
    // END CABANG & RUANG MANAGEMENT
}
