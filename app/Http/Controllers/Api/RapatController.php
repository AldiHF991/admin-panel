<?php

namespace App\Http\Controllers\Api;

use App\Events\AbsensiUpdated;
use App\Events\MeetingUpdated;
use App\Exports\AbsensiRapatExport;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Rapat;
use App\Models\RapatFile;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class RapatController extends Controller
{
    use AuthorizesRequests;

    // GET: /api/rapat
    public function index()
    {
        // PERBAIKAN: Tambahkan otorisasi dan muat relasi yang konsisten.
        $this->authorize('admin-auth');
        // Memuat relasi yang relevan, termasuk 'divisions'
        $rapat = Rapat::with(['cabang', 'room', 'status', 'pengaju', 'divisions'])->get();

        return response()->json($rapat);
    }

    // POST: /api/rapat
    public function store(Request $request)
    {
        // 1. Validasi input: hanya butuh division_ids, peserta_ids bisa dihapus
        $validatedData = $request->validate([
            'id_cabang' => 'required|exists:cabang,id',
            'id_room' => 'required|exists:room,id_room',
            'judul' => 'required|string|max:100',
            'tanggal' => 'required|date',
            'waktu_start' => 'required',
            'waktu_end' => 'nullable|after:waktu_start',
            'desc' => 'nullable|string|max:100',
            // Dihapus: 'peserta_ids' => 'nullable|array',
            // Dihapus: 'peserta_ids.*' => 'exists:users,id_user',
            'division_ids' => 'nullable|array', // Izinkan division_ids bernilai null atau array
            'division_ids.*' => 'nullable|exists:division,id_division', // Pastikan setiap ID ada di tabel divisions
            // PERBAIKAN: Tambahkan validasi untuk id_user_pengaju yang dikirim dari form
            'id_user_pengaju' => 'nullable|exists:users,id_user',
            // TAMBAHAN: Validasi untuk file upload
            'files_materi.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,rar,7z,mp4,mp3,wav|max:20480',
            'files_notulensi.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,rar,7z,mp4,mp3,wav|max:20480',
            'files_dokumentasi.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,rar,7z,mp4,mp3,wav|max:20480',
            'files_lainnya.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,rar,7z,mp4,mp3,wav|max:20480',
        ]);

        // 2. Ambil data user yang sedang login
        $user = Auth::user();

        // 3. Tambahkan id_user_pengaju dari user yang login
        $validatedData['id_user_pengaju'] = $user->id_user;
        // PERBAIKAN: Gunakan id_user_pengaju dari request jika ada dan tidak kosong.
        // Ini memungkinkan admin memilih PIC lain.
        if ($request->has('id_user_pengaju') && ! empty($request->id_user_pengaju)) {
            $validatedData['id_user_pengaju'] = $request->id_user_pengaju;
        }

        // 4. Terapkan logika untuk id_status berdasarkan role user
        if ($user->id_role == 1) { // Admin
            $validatedData['id_status'] = 1; // Langsung 'Diterima'
        } elseif ($user->id_role == 2) { // PIC
            $validatedData['id_status'] = 3; // 'Menunggu'
        } else {
            return response()->json([
                'message' => 'Anda tidak memiliki izin untuk membuat rapat.',
            ], 403);
        }

        // 5. Buat data rapat dengan data yang divalidasi
        $rapat = Rapat::create($validatedData);

        // 6. Lampirkan divisi yang diundang ke rapat
        if (isset($validatedData['division_ids']) && ! empty($validatedData['division_ids'])) {
            // Jika ada division_ids, lampirkan seperti biasa
            $rapat->divisions()->attach($validatedData['division_ids']);
        } else {
            // Jika division_ids null atau array kosong, buat entri dengan id_division = NULL
            $rapat->divisions()->attach([null]);
        }

        // 7. TAMBAHAN: Upload file untuk setiap kategori
        $this->uploadFiles($request->file('files_materi'), $rapat, 1);
        $this->uploadFiles($request->file('files_notulensi'), $rapat, 2);
        $this->uploadFiles($request->file('files_dokumentasi'), $rapat, 3);
        $this->uploadFiles($request->file('files_lainnya'), $rapat, 4);

        // 8. Kembalikan response
        return response()->json([
            'message' => 'Rapat berhasil dibuat.',
            // PERBAIKAN: Memuat relasi yang benar dan konsisten, termasuk 'cabang'
            'data' => $rapat->load(['cabang', 'room', 'status', 'pengaju', 'divisions']),
        ], 201);
    }

    // GET:
    public function show($id)
    {
        // PERBAIKAN: Memuat semua relasi yang dibutuhkan frontend, terutama 'cabang' dan 'divisions'.
        $rapat = Rapat::with(['cabang', 'room', 'status', 'pengaju', 'divisions', 'files'])->findOrFail($id);

        return response()->json($rapat);
    }

    // GET: /api/rapat/saya (Method Baru)
    public function rapatSaya()
    {
        // 1. Ambil ID user yang sedang login
        $userId = Auth::user()->id_user;

        // 2. Ambil data rapat yang memiliki 'id_user_pengaju' sama dengan ID user yang login
        $rapat = Rapat::with(['cabang', 'room', 'status', 'pengaju', 'divisions'])
            ->where('id_user_pengaju', $userId)
            ->orderBy('tanggal', 'desc')
            ->get();

        // 3. Kembalikan response dalam bentuk JSON
        return response()->json($rapat);
    }

    // PUT: /api/rapat/{id}
    public function update(Request $request, $id)
    {
        // PERBAIKAN: Tambahkan otorisasi untuk memastikan hanya admin yang bisa update.
        $this->authorize('admin-auth');

        $rapat = Rapat::findOrFail($id);

        // Validasi data yang masuk, mirip dengan store
        $validatedData = $request->validate([
            'id_cabang' => 'sometimes|required|exists:cabang,id',
            'id_room' => 'sometimes|required|exists:room,id_room',
            'judul' => 'sometimes|required|string|max:100',
            'tanggal' => 'sometimes|required|date',
            'waktu_start' => 'sometimes|required',
            'waktu_end' => 'sometimes|nullable|after:waktu_start',
            'desc' => 'nullable|string|max:100',
            'division_ids' => 'sometimes|array', // Tidak perlu 'required' karena bisa jadi tidak diubah
            'division_ids.*' => 'exists:division,id_division', // Validasi ke tabel division
            'id_status' => 'sometimes|required|exists:status_rapat,id_status', // Tambahan validasi untuk status
            // TAMBAHAN: Validasi untuk file upload
            'files_materi.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,rar,7z,mp4,mp3,wav|max:20480',
            'files_notulensi.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,rar,7z,mp4,mp3,wav|max:20480',
            'files_dokumentasi.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,rar,7z,mp4,mp3,wav|max:20480',
            'files_lainnya.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,rar,7z,mp4,mp3,wav|max:20480',
        ]);

        $rapat->update($validatedData);

        // Gunakan sync() untuk memperbarui daftar divisi.
        if ($request->has('division_ids')) {
            // PERBAIKAN: Menggunakan sync() pada relasi 'divisions'
            $rapat->divisions()->sync($validatedData['division_ids']);
        }

        // TAMBAHAN: Upload file baru jika ada
        $this->uploadFiles($request->file('files_materi'), $rapat, 1);
        $this->uploadFiles($request->file('files_notulensi'), $rapat, 2);
        $this->uploadFiles($request->file('files_dokumentasi'), $rapat, 3);
        $this->uploadFiles($request->file('files_lainnya'), $rapat, 4);

        return response()->json([
            'message' => 'Rapat berhasil diperbarui',
            'data' => $rapat->load(['cabang', 'room', 'status', 'pengaju', 'divisions']), // Muat ulang relasi setelah update
        ]);
    }

    // POST: /api/rapat/{id}/setujui
    public function setujuiRapat(Request $request, $id)
    {
        // PERBAIKAN: Aktifkan otorisasi untuk memastikan hanya admin yang bisa menyetujui.
        $this->authorize('admin-auth'); // Hanya Admin yang bisa menyetujui

        // 2. Cari rapat berdasarkan ID
        $rapat = Rapat::findOrFail($id);

        // 3. Ubah statusnya menjadi 'Disetujui' (Asumsi ID: 2)
        $rapat->id_status = 1;
        $rapat->save(); // Simpan perubahan

        // 4. Kembalikan response sukses
        return response()->json([
            'message' => 'Rapat berhasil disetujui.',
            'data' => $rapat,
        ]);
    }

    // (Opsional) Fungsi untuk menolak rapat
    // POST: /api/rapat/{id}/tolak
    public function tolakRapat(Request $request, $id)
    {
        $this->authorize('admin-auth'); // Hanya Admin yang bisa menyetujui

        $rapat = Rapat::findOrFail($id);

        // Asumsi: id_status = 5 adalah 'Ditolak'
        $rapat->id_status = 2;
        $rapat->save();

        return response()->json([
            'message' => 'Rapat telah ditolak.',
            'data' => $rapat,
        ]);
    }

    /**
     * Menyelesaikan rapat yang sedang berlangsung (terutama yang tidak menentu).
     * POST: /api/rapat/{id}/selesaikan
     */
    public function selesaikanRapat(Request $request, $id)
    {
        $rapat = Rapat::findOrFail($id);
        $user = Auth::user();

        // Otorisasi: Hanya admin atau pengaju rapat yang bisa menyelesaikan.
        if ($user->id_role != 1 && $user->id_user != $rapat->id_user_pengaju) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menyelesaikan rapat ini.'], 403);
        }

        // Update waktu selesai dan status
        // ID 5 adalah 'Selesai'
        $rapat->waktu_end = now()->format('H:i:s');
        $rapat->id_status = 5;
        $rapat->save();

        // Broadcast event
        MeetingUpdated::dispatch($rapat->fresh(['cabang', 'room', 'status', 'pengaju', 'divisions']), 'finished');

        return response()->json([
            'message' => 'Rapat berhasil diselesaikan.',
            'data' => $rapat->fresh(), // Mengambil data terbaru dari DB
        ]);
    }

    // DELETE: /api/rapat/{id}
    public function destroy($id)
    {
        // PERBAIKAN: Tambahkan otorisasi
        $this->authorize('admin-auth');
        $rapat = Rapat::findOrFail($id);
        $rapat->delete();

        return response()->json(['message' => 'Rapat berhasil dihapus']);
    }

    /**
     * Mengambil daftar peserta untuk rapat tertentu.
     * GET: /api/rapat/{id}/peserta
     */
    public function getPesertaRapat($id)
    {
        // PERBAIKAN: Mengambil dari relasi 'divisions'
        $rapat = Rapat::with('divisions:id_division,division_name')->findOrFail($id);

        return response()->json([
            'message' => 'Berhasil mengambil data peserta rapat.',
            'data' => $rapat->divisions, // 'divisions' sekarang berisi daftar divisi yang diundang
        ]);
    }

    /**
     * Menambahkan peserta ke rapat yang sudah ada.
     * POST: /api/rapat/{id}/peserta
     */
    public function addPesertaRapat(Request $request, $id)
    {
        // 1. Validasi input yang dikirim
        $validatedData = $request->validate([
            'division_ids' => 'required|array',
            'division_ids.*' => 'exists:division,id_division', // Pastikan setiap ID ada di tabel divisions
        ]);

        // 2. Cari rapat berdasarkan ID
        $rapat = Rapat::findOrFail($id);

        // 3. Gunakan syncWithoutDetaching() untuk menambahkan divisi baru tanpa menghapus yang lama.
        $rapat->divisions()->syncWithoutDetaching($validatedData['division_ids']);

        // 4. Kembalikan response sukses dengan daftar peserta yang telah diperbarui
        return response()->json([
            'message' => 'Peserta berhasil ditambahkan ke rapat.',
            'data' => $rapat->load('divisions')->divisions, // Muat ulang relasi divisions dan kirim datanya
        ]);
    }

    /**
     * Mengambil daftar absensi untuk rapat tertentu.
     * GET: /api/rapat/{id}/absensi
     */
    public function getAbsensiRapat($id)
    {
        // Pastikan rapat ada
        Rapat::findOrFail($id);

        // Ambil data absensi untuk rapat ini, sertakan data user yang absen via polymorphic
        $absensi = Absensi::where('id_rapat', $id)
            ->with('attendable') // Eager load data user via polymorphic
            ->orderBy('waktu_absen', 'asc')
            ->get();

        broadcast(new AbsensiUpdated($id, $absensi));

        return response()->json([
            'data' => $absensi,
        ]);
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

        // 3. Buat nama file yang deskriptif agar tidak bingung
        // contoh: laporan-absensi-rapat-koordinasi-2023-10-27.xlsx
        $fileName = 'laporan-absensi-'.Str::slug($rapat->judul).'-'.date('Y-m-d').'.xlsx';

        // 4. Gunakan class AbsensiRapatExport yang baru untuk men-download file
        // Koleksi $absensi diteruskan ke constructor class export.
        return Excel::download(new AbsensiRapatExport($absensi), $fileName);
    }

    /**
     * Helper function untuk upload file berdasarkan kategori.
     * 
     * @param array|null $files Array of uploaded files
     * @param Rapat $rapat Rapat instance
     * @param int $categoryId Category ID (1=Materi, 2=Notulensi, 3=Dokumentasi, 4=Lainnya)
     * @return void
     */
    private function uploadFiles($files, Rapat $rapat, int $categoryId)
    {
        if ($files) {
            foreach ($files as $file) {
                $path = $file->store('public/rapat_files/' . $rapat->id_rapat);
                RapatFile::create([
                    'id_rapat' => $rapat->id_rapat,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'id_categories' => $categoryId,
                ]);
            }
        }
    }

    /**
     * Menghasilkan Signed URL sementara untuk download file.
     * Endpoint ini harus dilindungi auth:sanctum.
     */
    public function getDownloadUrl(Request $request, RapatFile $file)
    {
        // 1. Cek otorisasi dasar (apakah user berhak akses rapat ini)
        $user = $request->user();
        
        // Logika otorisasi:
        // - Admin/PIC boleh akses
        // - Peserta yang sudah absen boleh akses (cek tabel absensi)
        // - Atau jika user terdaftar sebagai peserta rapat (via divisi)
        
        $canAccess = false;
        
        if ($user->id_role == 1 || $user->id_role == 2) {
            $canAccess = true;
        } else {
            // Cek apakah user sudah absen di rapat ini
            $hasAttended = Absensi::where('id_rapat', $file->id_rapat)
                ->where('attendable_id', $user->id_user)
                ->where('attendable_type', User::class)
                ->exists();
                
            if ($hasAttended) {
                $canAccess = true;
            }
        }

        if (!$canAccess) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengunduh file ini.'], 403);
        }

        // 2. Generate Signed URL yang valid selama 5 menit
        // URL ini akan mengarah ke route 'download.signed' di web.php
        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'download.signed', 
            now()->addMinutes(15), 
            ['file' => $file->id_file]
        );

        return response()->json([
            'download_url' => $url,
            'expires_in' => 15 * 60 // seconds
        ]);
    }

    /**
     * Melayani download file dari Signed URL.
     * Route ini harus dilindungi middleware 'signed'.
     */
    public function signedDownload(Request $request, RapatFile $file)
    {
        // Karena sudah melewati middleware 'signed', URL-nya valid dan belum expired.
        // Kita tinggal serve filenya.
        
        if (!Storage::exists($file->file_path)) {
            abort(404, 'File tidak ditemukan di storage.');
        }

        return Storage::download($file->file_path, $file->file_name);
    }

    /**
     * Delete a file from a meeting
     * DELETE /api/rapat/files/{fileId}
     */
    public function deleteFile($fileId)
    {
        try {
            // Find the file
            $file = RapatFile::findOrFail($fileId);
            
            // Get the meeting
            $rapat = Rapat::findOrFail($file->id_rapat);
            
            // Get current user
            $user = Auth::user();
            
            // Authorization: Allow if user is Admin, PIC of the meeting, or the uploader
            $isAdmin = $user->id_role == 1;
            $isPIC = $rapat->id_user_pengaju == $user->id_user;
            $isUploader = $file->uploaded_by == $user->id_user;
            
            if (!$isAdmin && !$isPIC && !$isUploader) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk menghapus file ini.',
                ], 403);
            }
            
            // Delete file from storage
            if (Storage::exists($file->file_path)) {
                Storage::delete($file->file_path);
            }
            
            // Delete record from database
            $file->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'File berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus file.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
