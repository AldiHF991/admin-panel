<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use AuthorizesRequests;

    public function landingPage()
    {
        return view('landing-page');
    }

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

            // Cek role user
            $user = Auth::user();
            
            // Capture browser/device information
            $userAgent = $request->header('User-Agent');
            $deviceName = $this->getBrowserName($userAgent);
            
            // Update login tracking
            $user->update([
                'device_name' => $deviceName,
                'last_login_at' => now(),
            ]);
            
            if ($user->id_role == 2) { // PIC
                return redirect()->route('pic.dashboard');
            }

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

    /**
     * Extract browser name from User-Agent string
     */
    private function getBrowserName($userAgent)
    {
        if (strpos($userAgent, 'Edg') !== false) {
            return 'Microsoft Edge';
        } elseif (strpos($userAgent, 'Chrome') !== false) {
            return 'Google Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            return 'Mozilla Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            return 'Safari';
        } elseif (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) {
            return 'Opera';
        } else {
            return 'Unknown Browser';
        }
    }

    // BUG, NEED FOR FIXED
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/adminpanel/login');
    }
}
