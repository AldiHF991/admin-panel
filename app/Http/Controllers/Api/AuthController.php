<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use AuthorizesRequests;

    public function register(Request $request)
    {

        $this->authorize('admin-auth'); // Hanya Admin yang bisa menyetujui

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

        // Anda mungkin tidak perlu membuat token untuk user yang baru dibuat
        // karena user ini tidak langsung login. Sesuaikan sesuai kebutuhan.
        // $token = $user->createToken('api_token')->plainTextToken;

        // Cukup kembalikan data user yang baru dibuat
        return response()->json([
            'message' => 'User created successfully by admin.',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $v = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
            // Device Data Validation
            'hardware_id' => 'required|string',
            'app_instance_id' => 'required|string',
            'manufacturer' => 'required|string',
            'model' => 'required|string',
            'os_version' => 'required|string',
            'build_id' => 'nullable|string',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $user = User::where('username', $request->username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // --- DEVICE BINDING LOGIC START ---

        // 1. Calculate Fingerprint
        $fingerprintSource = $request->hardware_id . '|' .
                             $request->manufacturer . '|' .
                             $request->model . '|' .
                             $request->os_version . '|' .
                             ($request->build_id ?? '');
        
        $deviceFingerprint = hash('sha256', $fingerprintSource);

        // 2. Check Existing Binding for User
        // Menggunakan UserDevice::where...
        $existingDevice = \App\Models\UserDevice::where('user_id', $user->id_user)
                                  ->where('is_active', true)
                                  ->first();

        if (!$existingDevice) {
            // LOGIN PERTAMA KALI: Bind device ini ke user
            \App\Models\UserDevice::create([
                'user_id' => $user->id_user,
                'device_fingerprint' => $deviceFingerprint,
                'hardware_id' => $request->hardware_id,
                'app_instance_id' => $request->app_instance_id,
                'manufacturer' => $request->manufacturer,
                'model' => $request->model,
                'os_version' => $request->os_version,
                'build_id' => $request->build_id,
                'is_active' => true,
                'locked_at' => now(),
            ]);
            
            // Allow login
        } else {
            // SUDAH PERNAH LOGIN: Cek konsistensi device
            if ($existingDevice->device_fingerprint === $deviceFingerprint) {
                // Device SAMA.
                // Cek apakah app_instance_id berubah (Reinstall?)
                if ($existingDevice->app_instance_id !== $request->app_instance_id) {
                    $existingDevice->update([
                        'app_instance_id' => $request->app_instance_id
                    ]);
                }
                // Allow login
            } else {
                // Device BEDA -> TOLAK
                return response()->json([
                    'message' => "Akun ini hanya dapat digunakan pada perangkat yang telah terdaftar. Hubungi admin untuk reset perangkat.",
                    'error_code' => 'DEVICE_MISMATCH'
                ], 403);
            }
        }

        // --- DEVICE BINDING LOGIC END ---

        // Update Device Info jika belum ada atau jika login dari device yang sama
        if (!$user->device_id) {
            // For Legacy Support: Map hardware_id to device_id column
            $user->device_id = $request->hardware_id; 
            $user->device_name = $request->device_name; // from frontend payload
        } elseif ($user->device_id !== $request->hardware_id) {
             // If user logged in with new device via reset logic, update it?
             // Or keep it in sync with active UserDevice?
             // Since we allowed login (logic above passed), let's sync legacy column
             $user->device_id = $request->hardware_id;
             $user->device_name = $request->device_name;
        }
        
        $user->last_login_at = now();
        $user->save();

        // PERBAIKAN: Muat relasi dengan eager loading
        $user->load(['role', 'division', 'activeDevice']);

        // Create Token with Ability or Metadata to store app_instance_id if needed
        // Since Sanctum uses database, we can just use normal token. 
        // Validation middleware will check DB UserDevice vs User vs Current Request header if we enforce valid requests.
        // But user said "Middleware membaca token -> ambil app_instance_id".
        // We will add a claim 'app_instance_id' to the token abilities or similar.
        // Sanctum: $user->createToken('name', ['ability']);
        // But better: Just rely on DB check in middleware using user->id.
        // HOWEVER, user requirement #5: "Middleware... membaca token -> ambil app_instance_id di token"
        // I will add it to the token NAME itself (hacky but works) OR just standard check.
        // Let's use the token name to store metadata safely: "android_app:<uuid>"
        $tokenName = 'device:' . $request->app_instance_id;
        $token = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id_user' => $user->id_user,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'gender' => $user->gender,
                'photo' => $user->photo,
                'id_role' => $user->id_role,
                'id_division' => $user->id_division,
                'role' => $user->role ? $user->role->role : null,
                'division' => $user->division ? $user->division->division_name : null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'last_login_at' => $user->last_login_at,
                // Return device info needed? Maybe not.
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function profile(Request $request)
    {
        // Load relasi untuk response profile yang lengkap
        $user = $request->user()->load(['role', 'division']);

        return response()->json([
            'id_user' => $user->id_user,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'gender' => $user->gender,
            'photo' => $user->photo,
            'id_role' => $user->id_role,
            'id_division' => $user->id_division,
            'role' => $user->role ? $user->role->role : null,
            'division' => $user->division ? $user->division->division_name : null,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ]);
    }

    public function getUsersWithRole(Request $request)
    {
        // 🔒 LANGKAH KEAMANAN: Pastikan hanya user yang berwenang (misal: admin)
        // yang bisa mengakses daftar pengguna ini. Anda perlu membuat Gate/Policy
        // bernama 'view-users' terlebih dahulu.
        $this->authorize('pic-auth');  // Hanya Admin yang bisa menyetujui

        // Mengambil semua user dengan id_role = 2
        // 'with' digunakan untuk eager loading agar tidak terjadi N+1 problem query
        $users = User::with(['role', 'division'])
            ->where('id_role', 2)
            ->get();

        // Mengembalikan data user dalam format JSON
        return response()->json(['users' => $users], 200);
    }

    public function getDivision(Request $request)
    {
        $this->authorize('pic-auth');

        // Mengambil semua divisi kecuali yang memiliki id_division = 2000.
        $divisions = \App\Models\Division::where('id_division', '!=', 2000)->get();

        return response()->json(['divisions' => $divisions], 200);
    }

    // public function getUsersByDivision(Request $request)
    // {
    //     // 🔒 LANGKAH KEAMANAN: Pastikan hanya user yang berwenang yang bisa mengakses.
    //     // Menggunakan policy yang sama dengan endpoint user lainnya untuk konsistensi.
    //     $this->authorize('pic-auth');
    //     // Hanya Admin yang bisa menyetujui

    //     // Mengambil semua user dengan id_division = 2002
    //     // 'with' digunakan untuk eager loading agar tidak terjadi N+1 problem query
    //     $users = User::with(['role', 'division'])
    //         ->where('id_division', 2002)
    //         ->get();

    //     // Mengembalikan data user dalam format JSON
    //     return response()->json(['users' => $users], 200);
    // }

}
