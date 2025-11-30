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
        // Assuming we might need status list, though usually new meetings have a default status
        
        return view('pic.meetings.create', compact('cabangs', 'rooms'));
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
            'files.*' => 'nullable|file|max:5120', // Max 5MB per file
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
        $rapat->id_user_pic = Auth::id(); 

        $rapat->save();

        // Handle File Uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('rapat_files', 'public');
                $rapat->files()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                ]);
            }
        }

        return redirect()->route('pic.meetings.index')->with('success', 'Rapat berhasil diajukan.');
    }

    public function show(Rapat $rapat)
    {
        // Ensure the user owns this meeting
        if ($rapat->id_user_pengaju != Auth::id()) {
            abort(403);
        }

        $rapat->load(['room', 'status', 'cabang', 'pengaju']);

        if (request()->ajax()) {
            return response()->json([
                'judul' => $rapat->judul,
                'status' => $rapat->status->status_rapat ?? '-',
                'status_class' => $rapat->id_status == 1 ? 'bg-success' : ($rapat->id_status == 2 ? 'bg-danger' : 'bg-warning'),
                'cabang' => $rapat->cabang->cabang ?? '-',
                'room' => $rapat->room->room ?? '-',
                'tanggal' => $rapat->tanggal,
                'waktu' => $rapat->waktu_start . ' - ' . $rapat->waktu_end,
                'desc' => $rapat->desc ?? '-',
                'pengaju' => $rapat->pengaju->name ?? '-',
                'urls' => [
                    'absensi' => route('pic.meetings.absensi', $rapat->id_rapat),
                    'qr' => route('pic.meetings.qr', $rapat->id_rapat),
                ]
            ]);
        }

        return view('pic.meetings.show', compact('rapat'));
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
    
    // Reuse logic for Absensi and QR Code
    // Ideally these should be in a service or trait, but for now we can duplicate or call if appropriate.
    // Since RapatController methods might be protected by Admin middleware, we should implement them here or ensure routes use this controller.

    public function showAbsensi(Rapat $rapat)
    {
        if ($rapat->id_user_pengaju != Auth::id()) {
            abort(403);
        }
        
        $absensi = $rapat->absensi()->with('user')->get();
        return view('pic.meetings.absensi', compact('rapat', 'absensi'));
    }

    public function showQrCode(Rapat $rapat)
    {
        if ($rapat->id_user_pengaju != Auth::id()) {
            abort(403);
        }
        
        // Generate QR Code content (e.g., link to guest login or attendance)
        // Assuming the same logic as Admin
        $url = route('meetings.guestLogin', $rapat->id_rapat);
        $qrCode = QrCode::size(300)->generate($url);

        return view('pic.meetings.qr', compact('rapat', 'qrCode'));
    }

    public function showRecentActivityReport()
    {
        $title = 'Laporan Aktivitas Rapat (3 Hari Terakhir)';

        // Mengambil semua data rapat dari 3 hari terakhir
        $rapatTigaHariTerakhir = Rapat::with(['pengaju', 'status', 'room'])
            ->where('tanggal', '>=', \Carbon\Carbon::now()->subDays(3)->toDateString())
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
