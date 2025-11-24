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
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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

        // 2. Ambil parameter dari request untuk filter dan pencarian
        $selectedRoleId = $request->input('id_role');
        $searchTerm = $request->input('search');
        $sort = $request->input('sort', 'created_at'); // Default sort by creation date
        $direction = $request->input('direction', 'desc'); // Default direction descending

        // 3. Siapkan query user
        $usersQuery = User::with(['role', 'division']);
        
        // Terapkan pengurutan
        $usersQuery->orderBy($sort, $direction);
        // 4. Terapkan filter berdasarkan role jika ada
        if ($selectedRoleId) {
            $usersQuery->where('id_role', $selectedRoleId);
        }

        // 5. Terapkan filter pencarian berdasarkan nama jika ada
        if ($searchTerm) {
            $usersQuery->where('name', 'like', '%' . $searchTerm . '%');
        }

        // 6. Lakukan paginasi dan tambahkan parameter query string ke link paginasi
        $users = $usersQuery->paginate(10)->appends($request->query());

        return view('users.user-management', compact('users', 'roles', 'divisions', 'sort', 'direction'));
    }

    public function showBranch(Request $request)
    {
        // 1. Ambil semua role untuk dropdown filter
        $statusRuangan = StatusRuangan::all();
        $cabang = Cabang::all();

        // 2. Ambil parameter dari request
        $selectedCabangId = $request->input('id_cabang');
        $searchTerm = $request->input('search');
        $sort = $request->input('sort', 'room.room'); // Default sort by room name
        $direction = $request->input('direction', 'asc'); // Default direction ascending

        // 3. Siapkan query ruangan
        $roomQuery = Room::with(['cabang', 'statusRuangan'])->select('room.*');

        // 4. Terapkan filter berdasarkan cabang jika ada
        if ($selectedCabangId) {
            $roomQuery->where('id_cabang', $selectedCabangId);
        }

        // 5. Terapkan filter pencarian berdasarkan nama ruangan jika ada
        if ($searchTerm) {
            $roomQuery->where('room', 'like', '%' . $searchTerm . '%');
        }

        // 6. Terapkan pengurutan (termasuk join untuk sort by alamat)
        if ($sort === 'cabang.alamat') {
            $roomQuery->join('cabang', 'room.id_cabang', '=', 'cabang.id')->orderBy('cabang.alamat', $direction);
        } else {
            $roomQuery->orderBy($sort, $direction);
        }

        // 7. Lakukan paginasi
        $rooms = $roomQuery->paginate(10)->appends($request->query());

        return view('branches.branch-room', compact('cabang', 'rooms', 'statusRuangan', 'sort', 'direction'));
    }

    public function showMeetings(Request $request)
    {
        $cabangs = Cabang::all();
        $rooms = Room::all();
        $divisions = Division::where('id_division', '!=', 2000)->get();
        $statuses = StatusRapat::all();
        $allRapats = Rapat::all(); // Data semua rapat untuk pengecekan di frontend

        // Ambil semua PIC, diurutkan berdasarkan nama
        $pics = User::where('id_role', 2)->orderBy('name', 'asc')->get();

        // Ambil parameter untuk filter, search, dan sort rapat
        $selectedPicId = $request->input('id_user_pic');
        $searchTerm = $request->input('search');
        $sort = $request->input('sort', 'tanggal'); // Default sort by tanggal
        $direction = $request->input('direction', 'desc'); // Default direction descending

        $rapatsQuery = Rapat::with(['cabang', 'room', 'status', 'pengaju', 'divisions']);

        // Terapkan filter berdasarkan PIC yang dipilih
        if ($selectedPicId) {
            $rapatsQuery->where('id_user_pengaju', $selectedPicId);
        }

        // Terapkan filter pencarian berdasarkan judul rapat
        if ($searchTerm) {
            $rapatsQuery->where('judul', 'like', '%' . $searchTerm . '%');
        }

        // Terapkan pengurutan, paginasi, dan ambil data rapat
        $rapats = $rapatsQuery->orderBy($sort, $direction)->paginate(10)->appends($request->query());

        return view('meetings.meeting-management', compact('rapats', 'pics', 'cabangs', 'rooms', 'divisions', 'statuses', 'allRapats', 'sort', 'direction', 'selectedPicId'));
    }

    // END Show FUNCTIONS

    // ACCOUNT MANAGEMENT FUNCTIONS START

    // Store User Account
    public function storeUserAccount(Request $request)
    {
        $v = Validator::make($request->all(), [
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:1',
            'name' => 'required|string',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'nullable|string',
            'id_role' => 'nullable|integer',
            'gender' => 'nullable|in:Male,Female',
            'id_division' => 'nullable|integer',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $data = $v->validated();

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 'public');
            $data['photo'] = Storage::url($path);
        }

        $data['password'] = Hash::make($data['password']);

        // Pengguna baru berhasil dibuat oleh admin
        $user = User::create($data);

             return redirect()->back()->with('success', 'User berhasil ditambahkan!');
    }

    // Update User Account
    public function updateUserAccount(Request $request)
    {
        $userId = $request->input('id_user');
        $user = User::findOrFail($userId);

        $v = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users,username,'.$userId.',id_user',
            'password' => 'nullable|string|min:1',
            'name' => 'required|string',
            'email' => 'required|string|email|max:255|unique:users,email,'.$userId.',id_user',
            'phone' => 'nullable|string',
            'id_role' => 'nullable|integer',
            'gender' => 'nullable|in:Male,Female',
            'id_division' => 'nullable|integer',
            'photo' => 'nullable|image|max:2048',
        ]);
        
        if ($v->fails()) {
            return redirect()->back()->withErrors($v)->withInput();
        }

        $data = $v->validated();

        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($user->photo && Storage::disk('public')->exists(str_replace('/storage/', '', $user->photo))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->photo));
            }
            $path = $request->file('photo')->store('photos', 'public');
            $data['photo'] = Storage::url($path);
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']); // Jangan update password jika kosong
        }

        $user->update($data);

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
            'room' => 'required|string|max:255|unique:room,room',
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
            'room' => 'required|string|max:255|unique:room,room,'.$request->id_room.',id_room',
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
