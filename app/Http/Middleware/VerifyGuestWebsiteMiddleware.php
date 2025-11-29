<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyGuestWebsiteMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ambil ID tamu dan rapat dari sesi yang disimpan saat login.
        $sessionGuestId = $request->session()->get('guest_id');
        $sessionRapatId = $request->session()->get('rapat_id');

        // Ambil ID tamu dan rapat dari parameter route.
        // Route model binding untuk {rapat} sudah menyediakan objek Rapat.
        $routeGuestId = $request->route('guest');
        $routeRapatId = $request->route('rapat')->id_rapat;

        // Validasi:
        // 1. Pastikan ada data sesi.
        // 2. Pastikan ID tamu dari sesi cocok dengan ID tamu di URL.
        // 3. Pastikan ID rapat dari sesi cocok dengan ID rapat di URL.
        if (! $sessionGuestId || $sessionGuestId != $routeGuestId || $sessionRapatId != $routeRapatId) {
            // Jika tidak valid, batalkan permintaan dan beri respons 'Forbidden'.
            abort(403, 'Akses Ditolak. Sesi Anda tidak valid untuk halaman ini.');
        }

        return $next($request);
    }
}
