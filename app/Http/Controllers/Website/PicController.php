<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Rapat;
use App\Models\Cabang;
use App\Models\Room;
use App\Models\StatusRapat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\User;
use App\Notifications\NewMeetingNotification;
use Illuminate\Support\Facades\Notification;

class PicController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        // Rapat Baru Dibuat (Latest requested meetings by this user)
        // Assuming "Rapat Baru Dibuat" for PIC means meetings they requested recently.
        // Or should it be global? "Rapat terbaru yang telah diajukan" implies meetings requested by *anyone*?
        // "sama seperti pada dashboardnya admin" - Admin sees ALL meetings.
        // But PIC usually only sees their own.
        // However, the request says "tabel aktivitas rapat terakhir, dan rapat terbaru yang telah diajukan (sama seperti pada dashboardnya admin)".
        // If it's "same as admin", maybe they want to see global activity?
        // But usually PIC is restricted.
        // Let's stick to the user's data for now to be safe, or maybe all meetings if it's a "dashboard" for general info.
        // Wait, "pic saya ingin di dashboardnya ada... rapat terbaru yang telah diajukan".
        // If I look at the Admin dashboard code:
        // $rapatBaruDibuat = Rapat::with(['room', 'pengaju', 'status'])->where('created_at', '>=', Carbon::now()->subDays(3))->latest()->get();
        // $rapatTigaHariTerakhir = Rapat::with(['room', 'pengaju', 'status'])->where('tanggal', '>=', Carbon::now()->subDays(3)->format('Y-m-d'))->orderBy('tanggal', 'asc')->orderBy('waktu_start', 'asc')->get();
        
        // If the user wants "same as admin", I should probably show them the same data but maybe restricted to what they are allowed to see?
        // Usually PIC only sees their own. But if they want a "dashboard", maybe they want to see room availability etc.
        // Let's assume for now they want to see *their* meetings or *all* meetings?
        // "rapat terbaru yang telah diajukan" -> "latest meetings requested".
        // If I am a PIC, I might want to see if the room is busy.
        // Let's try to fetch ALL meetings for the "Recent Activity" (so they know what's going on) but maybe only their own for "Newly Created"?
        // Actually, "Rapat Baru Dibuat" in admin is global.
        // Let's replicate the Admin logic but we might need to be careful about permissions.
        // Since the user said "sama seperti pada dashboardnya admin", I will fetch global data but maybe limit actions.
        // But wait, `PicController` usually filters by `id_user_pengaju`.
        // If I show other people's meetings, is that a privacy issue?
        // In many booking systems, you can see *that* a room is booked, but maybe not details.
        // However, the Admin dashboard shows "Pengaju".
        // Let's assume it's fine to show global data for "Activity" (Schedule) so they don't clash.
        // And "Newly Created" might be less useful if it's global, but maybe they want to see it.
        // I will implement it as Global for now because "sama seperti admin" is a strong hint.
        
        $rapatBaruDibuat = Rapat::with(['room', 'pengaju', 'status'])
            ->where('created_at', '>=', \Carbon\Carbon::now()->subDays(3))
            ->latest()
            ->get();

        $rapatTigaHariTerakhir = Rapat::with(['room', 'pengaju', 'status'])
            ->where('tanggal', '>=', \Carbon\Carbon::now()->subDays(3)->format('Y-m-d'))
            ->whereIn('id_status', [1, 4]) // Filter: Diterima (1) & Berlangsung (4)
            ->orderBy('tanggal', 'asc')
            ->orderBy('waktu_start', 'asc')
            ->get();

        return view('pic.dashboard', compact('rapatBaruDibuat', 'rapatTigaHariTerakhir'));
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Rapat::where('id_user_pengaju', $user->id_user)
            ->with(['room', 'status', 'cabang']);

        // Search by Judul
        if ($request->has('search') && $request->search != '') {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        // Sorting
        $sort = $request->get('sort', 'created_at'); // Default sort
        $direction = $request->get('direction', 'desc');

        if ($sort == 'status') {
            // Custom sort for status: Selesai (5), Berlangsung (4), Menunggu (3), Ditolak (2), Diterima (1)
            // Adjust order as per user request "selesai, berlangsung, menunggu, ditolak"
            // Assuming IDs: 5=Selesai, 4=Berlangsung, 3=Menunggu, 2=Ditolak, 1=Diterima
            $query->orderByRaw("FIELD(id_status, 5, 4, 3, 2, 1) " . $direction);
        } elseif (in_array($sort, ['judul', 'tanggal', 'waktu_start'])) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $meetings = $query->paginate(10)->withQueryString();
        $cabangs = Cabang::all();
        $allRapats = Rapat::all(); // For room availability check

        return view('pic.meetings.index', compact('meetings', 'cabangs', 'allRapats'));
    }

    public function create()
    {
        $cabangs = Cabang::all();
        $rooms = Room::all();
        $allRapats = Rapat::all(); // Needed for availability check
        
        return view('pic.meetings.create', compact('cabangs', 'rooms', 'allRapats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'id_cabang' => 'required|exists:cabang,id',
            'id_room' => 'required|exists:room,id_room',
            'tanggal' => 'required|date',
            'waktu_start' => 'required',
            'waktu_end' => 'required|after:waktu_start',
            'desc' => 'nullable|string',
            'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,rar,7z,mp4,mp3,wav|max:20480', // Max 5MB per file
        ]);

        $rapat = new Rapat();
        $rapat->judul = $request->judul;
        $rapat->id_cabang = $request->id_cabang;
        $rapat->id_room = $request->id_room;
        $rapat->tanggal = $request->tanggal;
        $rapat->waktu_start = $request->waktu_start;
        $rapat->waktu_end = $request->waktu_end;
        $rapat->desc = $request->desc;
        $rapat->id_user_pengaju = Auth::id();
        $rapat->id_status = 3; // Default status: Menunggu (Baru Diajukan)

        $rapat->save();

        // Kirim Notifikasi ke Admin
        $admins = User::where('id_role', 1)->get();
        if ($admins->count() > 0) {
            Notification::send($admins, new NewMeetingNotification($rapat, Auth::user()->nama));
        }

        // Handle File Uploads
        // Handle File Uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                // Samakan logic penyimpanan dengan RapatController (Admin)
                // Simpan ke 'storage/app/public/rapat_files/{id}'
                // Path di DB akan tersimpan sebagai 'public/rapat_files/{id}/{filename}'
                // Ini penting agar Storage::download() di RapatController (yang pakai default disk local) bisa menemukannya.
                $path = $file->store('public/rapat_files/'.$rapat->id_rapat);
                
                $rapat->files()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(), // atau getClientMimeType()
                    'file_size' => $file->getSize(), // Tambahkan file_size agar konsisten
                ]);
            }
        }

        return redirect()->route('pic.meetings.index')->with('success', 'Rapat berhasil diajukan.')->with('highlight_id', $rapat->id_rapat);
    }

    public function show(Rapat $rapat)
    {
        // Ensure the user owns this meeting
        if ($rapat->id_user_pengaju != Auth::id()) {
            if (request()->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403);
        }

        // Eagerly load all required relationships
        $rapat->load(['room', 'status', 'cabang', 'pengaju']);

        if (request()->ajax()) {
            try {
                $response = [
                    'judul' => $rapat->judul ?? '-',
                    'status' => $rapat->status ? $rapat->status->status_rapat : '-',
                    'status_class' => $rapat->id_status == 1 ? 'bg-success' : ($rapat->id_status == 2 ? 'bg-danger' : 'bg-warning'),
                    'rejection_note' => $rapat->rejection_note,
                    'cabang' => $rapat->cabang ? $rapat->cabang->cabang : '-',
                    'room' => $rapat->room ? $rapat->room->room : '-',
                    'tanggal' => $rapat->tanggal ?? '-',
                    'waktu' => ($rapat->waktu_start ?? '') . ' - ' . ($rapat->waktu_end ?? ''),
                    'desc' => $rapat->desc ?? '-',
                    'pengaju' => $rapat->pengaju ? ($rapat->pengaju->nama ?? $rapat->pengaju->name ?? '-') : '-',
                    'urls' => [
                        'absensi' => route('pic.meetings.absensi', $rapat->id_rapat),
                        'qr' => route('pic.meetings.qr', $rapat->id_rapat),
                    ]
                ];
                
                \Log::info('PicController::show response', $response);
                
                return response()->json($response);
            } catch (\Exception $e) {
                \Log::error('Error in PicController::show: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
                return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
            }
        }

        return redirect()->route('pic.meetings.index');
    }

    public function destroy($id)
    {
        $rapat = Rapat::findOrFail($id);

        // Ensure the user owns this meeting
        if ($rapat->id_user_pengaju != Auth::id()) {
            abort(403);
        }

        // Only allow deletion if status is "Baru Diajukan" (ID 3)
        if ($rapat->id_status != 3) {
            return back()->with('error', 'Hanya rapat yang baru diajukan yang dapat dihapus.');
        }

        $rapat->delete();

        return redirect()->route('pic.meetings.index')->with('success', 'Rapat berhasil dihapus.');
    }
    
    /**
     * Mark meeting as finished
     */
    public function finishMeeting(Rapat $rapat)
    {
        // Ensure the user owns this meeting
        if ($rapat->id_user_pengaju != Auth::id()) {
            abort(403);
        }

        // Only allow finishing if status is "Berlangsung" (ID 4)
        if ($rapat->id_status != 4) {
            return back()->with('error', 'Hanya rapat yang sedang berlangsung yang dapat diselesaikan.');
        }

        // Update status to "Selesai" (ID 5)
        $rapat->id_status = 5;
        $rapat->save();

        // Note: Room availability is automatically determined by checking meeting status and time
        // No need to manually update room status as it's based on active meetings

        return redirect()->route('pic.meetings.index')->with('success', 'Rapat berhasil diselesaikan. Ruangan kini tersedia kembali.');
    }
    
    // Reuse logic for Absensi and QR Code
    // Ideally these should be in a service or trait, but for now we can duplicate or call if appropriate.
    // Since RapatController methods might be protected by Admin middleware, we should implement them here or ensure routes use this controller.

    public function showAbsensi(Rapat $rapat)
    {
        if ($rapat->id_user_pengaju != Auth::id()) {
            abort(403);
        }
        
        // Load absensi with polymorphic attendable relationship
        $absensi = $rapat->absensi()->with('attendable')->get();
        
        // Load division for User attendables only
        $absensi->where('attendable_type', \App\Models\User::class)->load('attendable.division');
        
        return view('pic.meetings.absensi', compact('rapat', 'absensi'));
    }


    public function showQrCode(Rapat $rapat)
    {
        if ($rapat->id_user_pengaju != Auth::id()) {
            abort(403);
        }
        
        // Only allow QR code access for ongoing meetings (status = 4)
        if ($rapat->id_status != 4) {
            return redirect()->route('pic.meetings.index')
                ->with('error', 'QR Code Absensi hanya tersedia untuk rapat yang sedang berlangsung.');
        }
        
        return view('pic.meetings.qr', compact('rapat'));
    }

    public function showGuestQr(Rapat $rapat)
    {
        if ($rapat->id_user_pengaju != Auth::id()) {
            abort(403);
        }
        
        // Only allow guest QR access for ongoing meetings (status = 4)
        if ($rapat->id_status != 4) {
            return redirect()->route('pic.meetings.index')
                ->with('error', 'QR Mode Tamu hanya tersedia untuk rapat yang sedang berlangsung.');
        }
        
        // Membuat URL untuk halaman login tamu
        $guestUrl = route('meetings.guestLogin', $rapat->id_rapat);

        // Mengembalikan view dengan data yang diperlukan
        return view('pic.meetings.guest-qr', compact('rapat', 'guestUrl'));
    }

    /**
     * Mengembalikan SVG QR Code untuk rapat tertentu.
     * Digunakan untuk pembaruan AJAX di halaman display QR.
     */
    public function getQrCodeSvg(Rapat $rapat)
    {
        if ($rapat->id_user_pengaju != Auth::id()) {
            abort(403);
        }
        
        // Only generate QR for ongoing meetings (status = 4)
        if ($rapat->id_status != 4) {
            abort(403, 'QR Code only available for ongoing meetings');
        }
        
        // Pastikan rapat memiliki token
        $token = $rapat->current_qr_token ?? 'invalid-token';

        $svg = QrCode::size(400)->generate($token);

        return response($svg)->header('Content-Type', 'image/svg+xml');
    }

    public function showRecentActivityReport()
    {
        $title = 'Laporan Aktivitas Rapat (3 Hari Terakhir)';

        // Mengambil semua data rapat dari 3 hari terakhir
        $rapatTigaHariTerakhir = Rapat::with(['pengaju', 'status', 'room'])
            ->where('tanggal', '>=', \Carbon\Carbon::now()->subDays(3)->toDateString())
            ->whereIn('id_status', [1, 4]) // Filter: Diterima (1) & Berlangsung (4)
            ->orderBy('tanggal', 'desc')
            ->orderBy('waktu_start', 'desc')
            ->get();

        // Mengirim data ke view khusus laporan
        return view('reports.recent-activity', compact('rapatTigaHariTerakhir', 'title') + ['backRoute' => 'pic.dashboard']);
    }

    public function showNewlyCreatedReport()
    {
        $rapatBaruDibuat = Rapat::with(['room', 'pengaju', 'status'])
            ->where('created_at', '>=', \Carbon\Carbon::now()->subDays(3))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('reports.newly-created', [
            'title' => 'Laporan Rapat Baru Dibuat',
            'rapatBaruDibuat' => $rapatBaruDibuat,
            'backRoute' => 'pic.dashboard'
        ]);
    }
}
