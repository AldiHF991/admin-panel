<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use AuthorizesRequests;

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {

        $v = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $credentials = $request->only('username', 'password');

        // Coba untuk melakukan otentikasi
        if (Auth::attempt($credentials)) {
            // Otentikasi berhasil.
            // Regenerasi session untuk keamanan.
            $request->session()->regenerate();

            // Alihkan ke route 'dashboard' yang akan memanggil AdminController@showDashboard
            return redirect()->route('dashboard');
        }

        // Jika otentikasi gagal, kembalikan ke halaman login dengan pesan error.
        // Menggunakan `withErrors` untuk mengirim pesan error spesifik.
        // Anda bisa menampilkan error ini di view login Anda.
        return back()->withErrors([
            'username' => 'Username atau password yang diberikan tidak cocok.',
        ])->onlyInput('username');
    }

    // BUG, NEED FOR FIXED
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
