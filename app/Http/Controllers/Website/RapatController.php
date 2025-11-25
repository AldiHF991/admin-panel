<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Rapat;
use App\Models\RapatFile;
use App\Models\Cabang;
use App\Models\Absensi;
use App\Models\User;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsensiRapatExport;
use Carbon\Carbon;

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
            $query->where('judul', 'like', '%' . $request->search . '%');
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
        // Ambil data absensi awal untuk rapat ini
        $initialAbsensi = Absensi::with('user')
            ->where('id_rapat', $rapat->id_rapat)
            ->orderBy('waktu_absen', 'asc')
            ->get();

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
            'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,ppt,pptx|max:5120', // Maks 5MB per file
        ]);

        // Set status default ke 'Diterima' karena dibuat oleh Admin
        $validatedData['id_status'] = 1;

        $rapat = Rapat::create($validatedData);

        // Proses upload file jika ada
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('public/rapat_files/' . $rapat->id_rapat);
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
            $redirectUrl .= '?id_user_pic=' . $request->id_user_pengaju;
        }
        return redirect($redirectUrl)->with('success', 'Rapat berhasil ditambahkan.');
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
            'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,ppt,pptx|max:5120', // Maks 5MB per file
        ]);

        $rapat->update($validatedData);

        // Proses upload file baru jika ada
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('public/rapat_files/' . $rapat->id_rapat);
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
            $redirectUrl .= '?id_user_pic=' . $rapat->id_user_pengaju;
        }
        return redirect($redirectUrl)->with('success', 'Rapat berhasil diperbarui.');
    }

    public function destroy($id)
    {
        try {
            $rapat = Rapat::findOrFail($id);

            // Hapus folder file terkait di storage
            Storage::deleteDirectory('public/rapat_files/' . $rapat->id_rapat);

            // Hapus record file dari database (relasi sudah di-handle jika di-setting onDelete('cascade'))
            // Jika tidak, hapus manual: $rapat->files()->delete();
            $rapat->delete();

            return redirect()->route('meetings.index')->with('success', 'Rapat berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('meetings.index')->with('error', 'Gagal menghapus rapat. ' . $e->getMessage());
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
                'error' => $e->getMessage()
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
                'error' => $e->getMessage()
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
            ->with('user:id_user,name,email,id_division', 'user.division:id_division,division_name')
            ->orderBy('waktu_absen', 'asc')
            ->get();

        // 3. Buat nama file yang deskriptif agar tidak bingung
        // contoh: laporan-absensi-rapat-koordinasi-2023-10-27.xlsx
        $fileName = 'laporan-absensi-' . Str::slug($rapat->judul) . '-' . date('Y-m-d') . '.xlsx';

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
}
