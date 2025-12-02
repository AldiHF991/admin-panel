<?php

namespace App\Http\Controllers\Website;

use App\Exports\AbsensiRapatExport;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Cabang;
use App\Models\Guest;
use App\Models\Rapat;
use App\Models\RapatFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RapatController extends Controller
{
    public function index(Request $request)
    {
        $pics = User::where('id_role', 2)->get(); // Assuming PIC is a role
        $cabangs = Cabang::all();
        $statuses = \App\Models\StatusRapat::all();

        // Ambil semua rapat untuk pengecekan jadwal di JS
        $allRapats = Rapat::with('files')->select('id_rapat', 'id_room', 'tanggal', 'waktu_start', 'waktu_end')->get();

        // Query dasar
        $query = Rapat::with(['cabang', 'room', 'status', 'userPengaju', 'files']);

        // Filter berdasarkan PIC
        if ($request->filled('id_user_pic')) {
            $query->where('id_user_pengaju', $request->id_user_pic);
        }

        // Filter berdasarkan pencarian judul
        if ($request->filled('search')) {
            $query->where('judul', 'like', '%'.$request->search.'%');
        }

        // Logika Sorting
        $sort = $request->get('sort', 'judul'); // Default sort by judul
        $direction = $request->get('direction', 'asc'); // Default ascending
        $query->orderBy($sort, $direction);

        // Terapkan paginasi dengan limit 10
        $rapats = $query->paginate(10);

        return view('meetings.meeting-management', compact('pics', 'cabangs', 'statuses', 'rapats', 'allRapats', 'sort', 'direction'));
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

    /**
     * Mengembalikan SVG QR Code untuk rapat tertentu.
     * Digunakan untuk pembaruan AJAX di halaman display QR.
     */
    public function getQrCodeSvg(Rapat $rapat)
    {
        // Pastikan rapat memiliki token
        $token = $rapat->current_qr_token ?? 'invalid-token';

        $svg = QrCode::size(400)->generate($token);

        return response($svg)->header('Content-Type', 'image/svg+xml');
    }

    /**
     * Menampilkan halaman display absensi untuk rapat tertentu.
     */
    public function showAbsensi(Rapat $rapat)
    {
        // 1. Ambil semua data absensi dengan relasi polimorfik 'attendable' (User & Guest).
        $initialAbsensi = Absensi::with('attendable')
            ->where('id_rapat', $rapat->id_rapat)
            ->orderBy('waktu_absen', 'asc')
            ->get();

        // 2. Pisahkan absensi milik User dan muat relasi 'division' secara eksplisit.
        // Ini mencegah error karena kita tidak mencoba memuat 'division' pada model Guest.
        $initialAbsensi->where('attendable_type', User::class)
            ->load('attendable.division');

        // Menggunakan view baru yang akan kita buat
        // Mengirimkan variabel rapatId dan initialAbsensi ke view
        return view('meetings.absensi', ['rapatId' => $rapat->id_rapat, 'initialAbsensi' => $initialAbsensi]);
    }

    public function store(Request $request)
    {
        // Validasi data yang masuk
        $validatedData = $request->validate([
            'id_cabang' => 'nullable|exists:cabang,id',
            'id_room' => 'nullable|exists:room,id_room',
            'judul' => 'required|string|max:100',
            'tanggal' => 'required|date',
            'waktu_start' => 'required',
            'waktu_end' => 'nullable|after:waktu_start',
            'id_user_pengaju' => 'required|exists:users,id_user',
            'desc' => 'nullable|string|max:255',
            'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,rar,7z,mp4,mp3,wav|max:20480',// Maks 5MB per file
        ]);

        // Set status default ke 'Diterima' karena dibuat oleh Admin
        $validatedData['id_status'] = 1;

        $rapat = Rapat::create($validatedData);

        // Proses upload file jika ada
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('public/rapat_files/'.$rapat->id_rapat);
                RapatFile::create([
                    'id_rapat' => $rapat->id_rapat,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        // Redirect kembali ke halaman manajemen dengan query string PIC yang sama
        $redirectUrl = route('meetings.index');
        if ($request->id_user_pengaju) {
            $redirectUrl .= '?id_user_pic='.$request->id_user_pengaju;
        }

        // TAMBAHKAN 'highlight_id' ke session saat redirect
        return redirect($redirectUrl)->with('success', 'Rapat berhasil ditambahkan.')
            ->with('highlight_id', $rapat->id_rapat);
    }

    public function update(Request $request, Rapat $rapat)
    {
        // Validasi data yang masuk
        $validatedData = $request->validate([
            'id_cabang' => 'nullable|exists:cabang,id',
            'id_room' => 'nullable|exists:room,id_room',
            'judul' => 'required|string|max:100',
            'tanggal' => 'required|date',
            'waktu_start' => 'required',
            'waktu_end' => 'nullable|after:waktu_start',
            'desc' => 'nullable|string|max:255',
            'id_status' => 'required|exists:status_rapat,id_status',
            'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,rar,7z,mp4,mp3,wav|max:20480', // Maks 5MB per file
        ]);

        $rapat->update($validatedData);

        // Proses upload file baru jika ada
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('public/rapat_files/'.$rapat->id_rapat);
                RapatFile::create([
                    'id_rapat' => $rapat->id_rapat,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        // Redirect kembali ke halaman manajemen dengan query string PIC yang sama
        $redirectUrl = route('meetings.index');
        if ($rapat->id_user_pengaju) {
            $redirectUrl .= '?id_user_pic='.$rapat->id_user_pengaju;
        }

        // TAMBAHKAN 'highlight_id' ke session saat redirect
        return redirect($redirectUrl)->with('success', 'Rapat berhasil diperbarui.')
            ->with('highlight_id', $rapat->id_rapat);
    }

    public function destroy($id)
    {
        try {
            $rapat = Rapat::findOrFail($id);

            // Hapus folder file terkait di storage
            Storage::deleteDirectory('public/rapat_files/'.$rapat->id_rapat);

            // Hapus record file dari database (relasi sudah di-handle jika di-setting onDelete('cascade'))
            // Jika tidak, hapus manual: $rapat->files()->delete();
            $rapat->delete();

            return redirect()->route('meetings.index')->with('success', 'Rapat berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('meetings.index')->with('error', 'Gagal menghapus rapat. '.$e->getMessage());
        }
    }

    public function destroyFile(RapatFile $file)
    {
        try {
            // Hapus file dari storage
            Storage::delete($file->file_path);
            // Hapus record dari database
            $file->delete();

            return response()->json(['success' => true, 'message' => 'File berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus file.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getRoomsByCabang(Cabang $cabang)
    {
        try {
            // Mengambil ruangan yang berelasi dengan $cabang
            $rooms = $cabang->room()
                ->with('statusRuangan')
                ->get();

            return response()->json($rooms, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data ruangan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengekspor data absensi rapat ke Excel.
     * GET: /api/rapat/{id}/export-absensi
     */
    public function exportAbsensi($id)
    {
        // 1. Pastikan rapat ada
        $rapat = Rapat::findOrFail($id);

        // 2. Ambil data absensi untuk rapat ini, sertakan data user dan divisi.
        // Ini mirip dengan getAbsensiRapat, tapi dengan lebih banyak relasi.
        $absensi = Absensi::where('id_rapat', $id)
            ->with('attendable')
            ->orderBy('waktu_absen', 'asc')
            ->get();
            
        // Load division for User attendables
        $absensi->where('attendable_type', User::class)->load('attendable.division');

        // 3. Buat nama file yang deskriptif agar tidak bingung
        // contoh: laporan-absensi-rapat-koordinasi-2023-10-27.xlsx
        $fileName = 'laporan-absensi-'.Str::slug($rapat->judul).'-'.date('Y-m-d').'.xlsx';

        // 4. Gunakan class AbsensiRapatExport yang baru untuk men-download file
        // Koleksi $absensi diteruskan ke constructor class export.
        return Excel::download(new AbsensiRapatExport($absensi), $fileName);
    }

    /**
     * Mengambil file yang terikat pada sebuah rapat.
     * Digunakan oleh AJAX call dari modal edit.
     */
    public function getFiles($id)
    {
        // Menggunakan findOrFail untuk otomatis menangani jika rapat tidak ditemukan
        $rapat = Rapat::with('files')->findOrFail($id);

        // Memformat data file agar sesuai dengan yang dibutuhkan di frontend
        $files = $rapat->files->map(function ($file) {
            return [
                'id_file' => $file->id_file,
                'file_name' => $file->file_name,
                // Menghapus 'public/' dari path agar URL di frontend benar
                'file_path' => str_replace('public/', '', $file->file_path),
                // TAMBAHAN: Sertakan tipe file untuk ikon di frontend
                'file_type' => $file->file_type,
            ];
        });

        // Mengembalikan data file sebagai JSON
        return response()->json($files);
    }

    /**
     * Menampilkan halaman login tamu untuk rapat tertentu.
     */
    public function showGuestLogin(Rapat $rapat)
    {
        // Menggunakan view yang sama dengan guest umum, tapi mengirimkan data rapat
        return view('auth.guest', ['rapat' => $rapat]);
    }

    /**
     * Menyimpan data tamu dari formulir login tamu.
     * - Jika device_token BELUM pernah dipakai di rapat ini -> buat guest + absensi baru.
     * - Jika device_token SUDAH pernah dipakai -> anggap "login ulang" dan langsung ke dashboard.
     */
    public function storeGuest(Request $request, Rapat $rapat)
    {
        // 1. Validasi input
        $validatedData = $request->validate([
            'nama' => 'required|string|max:100',
            'asal_instansi' => 'required|string|max:100',
            'jabatan' => 'required|string|max:100',
            'nomor' => 'nullable|string|max:25',
            'device_token' => 'required|string|max:191',
        ]);

        $deviceToken = $validatedData['device_token'];

        // 2. Ambil user agent & ip address
        $userAgent = $request->userAgent();
        $ipAddress = $request->ip();

        // 3. Cek apakah device_token ini SUDAH pernah dipakai di rapat ini
        $existingAbsensi = Absensi::where('id_rapat', $rapat->id_rapat)
            ->where('device_token', $deviceToken)
            ->with('attendable') // pastikan ada relasi morphTo attendable di model Absensi
            ->first();

        if ($existingAbsensi) {
            // Ambil guest yang terkait dengan absensi ini
            $guest = $existingAbsensi->attendable; // harusnya instance App\Models\Guest

            // Set session lagi supaya middleware mengizinkan akses dashboard
            $request->session()->put('guest_id', $guest->id_guest);
            $request->session()->put('rapat_id', $rapat->id_rapat);

            // ⬇⬇⬇ di sini pesan bahwa dia sudah pernah isi form
            return redirect()->route('meetings.guestDashboard', [
                'rapat' => $rapat->id_rapat,
                'guest' => $guest->id_guest,
            ])->with('info', 'Perangkat ini sudah tercatat hadir pada rapat ini. Anda tidak dapat mengisi ulang formulir dan akan diarahkan ke dashboard.');
        }

        // 4. Kalau BELUM pernah absen dengan device_token ini -> buat guest baru
        $guest = Guest::create([
            'nama' => $validatedData['nama'],
            'asal_instansi' => $validatedData['asal_instansi'],
            'jabatan' => $validatedData['jabatan'],
            'nomor' => $validatedData['nomor'] ?? null,
        ]);

        // 5. Buat entri absensi untuk guest yang baru dibuat + simpan data keamanan
        $guest->absensi()->create([
            'id_rapat' => $rapat->id_rapat,
            'waktu_absen' => now(),
            'id_status_kehadiran' => 2, // 2 = Hadir (asumsi)

            'device_id_log' => null,           // guest tidak punya HWID
            'device_token' => $deviceToken,
            'user_agent' => $userAgent,
            'ip_address' => $ipAddress,
        ]);

        // 6. Simpan info ke sesi
        $request->session()->put('guest_id', $guest->id_guest);
        $request->session()->put('rapat_id', $rapat->id_rapat);

        // 7. Dispatch event untuk update realtime daftar absensi
        $allAbsensi = Absensi::where('id_rapat', $rapat->id_rapat)
            ->with('attendable')
            ->orderBy('waktu_absen', 'asc')
            ->get();

        \App\Events\AbsensiUpdated::dispatch($allAbsensi);

        // 8. Redirect ke dashboard guest
        return redirect()->route('meetings.guestDashboard', [
            'rapat' => $rapat->id_rapat,
            'guest' => $guest->id_guest,
        ])->with('success', 'Terima kasih, '.$validatedData['nama'].'. Kehadiran Anda telah berhasil dicatat.');
    }

    /**
     * Menampilkan dashboard guest setelah berhasil absen.
     */
    public function showGuestDashboard(Rapat $rapat, $guest)
    {
        $rapat->load(['cabang', 'room', 'files']);
        $guest = Guest::findOrFail($guest);

        return view('guest.dashboard', compact('rapat', 'guest'));
    }

    public function showGuestQr($id)
    {
        $rapat = Rapat::findOrFail($id);
        // Membuat URL untuk halaman login tamu
        $guestUrl = route('meetings.guestLogin', $rapat->id_rapat);

        // Mengembalikan view baru dengan data yang diperlukan
        return view('meetings.guest-qr', compact('rapat', 'guestUrl'));
    }

    /**
     * Menghapus sesi tamu dan mengalihkannya ke halaman login.
     */
    public function logoutGuest(Request $request, Rapat $rapat)
    {
        // Hapus data spesifik dari sesi yang digunakan untuk otentikasi tamu
        $request->session()->forget(['guest_id', 'rapat_id']);

        // Invalidate sesi untuk menghapus semua data sesi
        $request->session()->invalidate();

        // Regenerasi token untuk keamanan dari serangan CSRF
        $request->session()->regenerateToken();

        // Redirect ke halaman login tamu untuk rapat yang sama dengan pesan sukses
        return redirect()->route('meetings.guestLogin', ['rapat' => $rapat->id_rapat])
            ->with('success', 'Anda telah berhasil logout.');
    }

    /**
     * Menangani permintaan unduhan file yang aman.
     * Metode ini dipanggil oleh route yang dilindungi middleware 'auth'.
     */
    public function downloadFile(RapatFile $file)
    {
        // 1. Cek Otorisasi: User Login ATAU Guest yang valid untuk rapat ini
        $isAuthorized = false;

        if (\Illuminate\Support\Facades\Auth::check()) {
            $isAuthorized = true;
        } else {
            // Cek sesi guest
            $guestId = session('guest_id');
            $rapatId = session('rapat_id');
            
            // Pastikan guest sedang login di sesi rapat yang SAMA dengan file ini
            if ($guestId && $rapatId && $rapatId == $file->id_rapat) {
                $isAuthorized = true;
            }
        }

        if (!$isAuthorized) {
            // Jika tidak punya akses, redirect ke login atau 403
            // Karena ini akses file, 403 atau 404 lebih tepat untuk keamanan, tapi user minta "terpental ke login" diperbaiki.
            // Kita return 403 Forbidden.
            abort(403, 'Anda tidak memiliki izin untuk mengunduh file ini.');
        }

        // Pengecekan keberadaan file tetap penting untuk keamanan.
        // $file->file_path berisi path relatif dari 'storage/app/', contoh: 'public/rapat_files/...'
        if (! Storage::exists($file->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        // PERBAIKAN: Gunakan Storage::download() untuk keamanan dan keandalan.
        // Metode ini menangani path secara internal dan lebih aman daripada response()->download(storage_path(...)).
        // Argumen pertama adalah path dari storage, argumen kedua adalah nama file yang akan dilihat pengguna.
        return Storage::download($file->file_path, $file->file_name);
    }
    /**
     * Menerima pengajuan rapat (Set status ke Diterima / 1).
     */
    public function accept(Rapat $rapat)
    {
        try {
            $rapat->update(['id_status' => 1]); // 1 = Diterima
            
            // Tandai notifikasi terkait sebagai sudah dibaca (untuk admin)
            $notification = auth()->user()->notifications()
                ->where('data->meeting_id', $rapat->id_rapat)
                ->first();
                
            if ($notification) {
                $notification->markAsRead();
            }
            
            // Kirim notifikasi ke PIC bahwa rapat diterima
            if ($rapat->id_user_pengaju) {
                $pic = User::find($rapat->id_user_pengaju);
                if ($pic) {
                    $pic->notify(new \App\Notifications\MeetingStatusNotification($rapat, 'Rapat Diterima'));
                }
            }
            
            return back()->with('success', 'Rapat berhasil diterima.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menerima rapat: ' . $e->getMessage());
        }
    }

    /**
     * Menolak pengajuan rapat (Set status ke Ditolak / 2).
     */
    public function reject(Request $request, Rapat $rapat)
    {
        try {
            $request->validate([
                'rejection_note' => 'nullable|string|max:255',
            ]);

            $rapat->update([
                'id_status' => 2, // 2 = Ditolak
                'rejection_note' => $request->rejection_note,
            ]); 
            
            // Tandai notifikasi terkait sebagai sudah dibaca (untuk admin)
            $notification = auth()->user()->notifications()
                ->where('data->meeting_id', $rapat->id_rapat)
                ->first();
                
            if ($notification) {
                $notification->markAsRead();
            }
            
            // Kirim notifikasi ke PIC bahwa rapat ditolak
            if ($rapat->id_user_pengaju) {
                $pic = User::find($rapat->id_user_pengaju);
                if ($pic) {
                    $pic->notify(new \App\Notifications\MeetingStatusNotification($rapat, 'Rapat Ditolak', $request->rejection_note));
                }
            }
            
            return back()->with('success', 'Rapat berhasil ditolak.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menolak rapat: ' . $e->getMessage());
        }
    }
}
