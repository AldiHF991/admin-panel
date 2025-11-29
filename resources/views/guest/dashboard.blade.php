<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Tamu - {{ $rapat->judul }} | BBWS Brantas</title>
    <link rel="icon" href="{{ asset('images/logo_qr.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --pu-blue-dark: #020617;
            --pu-blue-main: #003366;
            --pu-blue-light: #0ea5e9;
            --pu-yellow: #F4C430;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", sans-serif;
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(14,165,233,.25), transparent 55%),
                radial-gradient(circle at bottom right, rgba(244,196,48,.18), transparent 55%),
                #020617;
            color: #111827;
            overflow: hidden; /* akan diubah ke auto setelah splash hilang */
        }

        .container-main {
            max-width: 960px;
            margin: 0 auto;
            padding: 1.75rem 1rem 2.5rem;
        }

        /* ========== SPLASH SCREEN FULLSCREEN ========== */
        #splash-screen {
            position: fixed;
            inset: 0;
            z-index: 999;
            background:
                radial-gradient(circle at top, rgba(148,163,184,0.25), transparent 55%),
                radial-gradient(circle at bottom, rgba(15,118,110,0.35), transparent 60%),
                #020617;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            text-align: center;
            opacity: 1;
            visibility: visible;
            transition: opacity 0.6s ease, visibility 0.6s ease;
        }

        #splash-screen.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .splash-card {
            max-width: 420px;
            width: 100%;
            background: rgba(15,23,42,0.85);
            border-radius: 18px;
            border: 1px solid rgba(148,163,184,0.35);
            padding: 1.75rem 1.5rem 1.5rem;
            box-shadow: 0 20px 50px rgba(15,23,42,0.7);
            backdrop-filter: blur(12px);
        }

        .splash-logo {
            width: 70px;
            height: 70px;
            border-radius: 999px;
            background: radial-gradient(circle at 30% 20%, #ffffff, #e2e8f0);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            overflow: hidden;
        }

        .splash-logo img {
            width: 52px;
            height: auto;
        }

        .splash-welcome {
            font-size: 1rem;
            font-weight: 500;
            color: #e5e7eb;
            margin-bottom: 0.35rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .splash-name {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.35rem;
            background: linear-gradient(135deg, #facc15, #fde68a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .splash-subtitle {
            font-size: 0.95rem;
            color: #cbd5f5;
            margin-bottom: 0.75rem;
        }

        .splash-rapat {
            font-size: 0.9rem;
            color: #94a3b8;
        }

        .splash-rapat span {
            display: block;
            font-weight: 600;
            color: #e5e7eb;
        }

        .splash-loader {
            margin-top: 1.25rem;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.8rem;
            color: #9ca3af;
        }

        .dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background-color: #facc15;
            animation: bounce 1s infinite ease-in-out;
        }
        .dot:nth-child(2) { animation-delay: 0.15s; }
        .dot:nth-child(3) { animation-delay: 0.3s; }

        @keyframes bounce {
            0%, 80%, 100% { transform: translateY(0); opacity: .4; }
            40% { transform: translateY(-6px); opacity: 1; }
        }

        /* ========== DASHBOARD CONTENT ========== */
        #dashboard-content {
            opacity: 0;
            transform: translateY(12px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        #dashboard-content.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Logo di pojok kiri atas */
        .main-logo {
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
            display: flex;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .main-logo.visible {
            opacity: 1;
        }

        .main-logo img {
            width: 30px;
            height: auto;
        }

        /* Header utama (logo + judul) */
        .main-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
        }
        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .btn-logout {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 0.5rem 0.85rem;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-logout:hover {
            background-color: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.4);
        }
        .btn-logout i {
            font-size: 1rem;
        }
        .header-mini {
            color: #e5e7eb;
        }

        .header-mini-title {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .header-mini-caption {
            font-size: 0.9rem;
            color: #9ca3af;
        }

        /* Card */
        .card-custom {
            background-color: #ffffff;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            padding: 1.5rem 1.4rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 14px 35px rgba(15,23,42,0.22);
        }

        .card-title-custom {
            font-size: 1.15rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .card-title-custom i {
            font-size: 1.2rem;
            color: var(--pu-blue-main);
        }

        /* Info list */
        .info-item {
            display: flex;
            align-items: flex-start;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background-color: #eef2ff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }

        .info-icon i {
            font-size: 1rem;
            color: var(--pu-blue-main);
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #9ca3af;
            margin-bottom: 0.2rem;
        }

        .info-value {
            font-size: 0.98rem;
            color: #111827;
            font-weight: 500;
        }

        /* File list */
        .file-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .file-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 0.85rem;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
            text-decoration: none;
            color: inherit;
            transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.1s;
        }

        .file-item:hover {
            background-color: #eef2ff;
            border-color: #d1d5db;
            transform: translateY(-1px);
        }

        .file-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background-color: #e5edff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }

        .file-icon i {
            font-size: 1.3rem;
            color: var(--pu-blue-main);
        }

        .file-info {
            flex: 1;
            min-width: 0;
        }

        .file-name {
            font-size: 0.95rem;
            font-weight: 500;
            color: #111827;
            margin-bottom: 0.15rem;
            word-break: break-word;
        }

        .file-size {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .file-download {
            margin-left: 0.5rem;
            flex-shrink: 0;
        }

        .file-download i {
            font-size: 1.1rem;
            color: #6b7280;
        }

        .file-item:hover .file-download i {
            color: var(--pu-blue-light);
        }

        .no-files {
            font-size: 0.95rem;
            color: #6b7280;
            text-align: center;
            padding: 1.5rem 0.75rem;
        }

        .no-files i {
            display: block;
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #d1d5db;
        }

        @media (max-width: 768px) {
            .container-main {
                padding: 1.25rem 1rem 2rem;
            }

            .card-custom {
                padding: 1.25rem 1.1rem;
            }

            .splash-card {
                padding: 1.5rem 1.25rem 1.25rem;
            }

            .splash-name {
                font-size: 1.55rem;
            }
        }
    </style>
</head>
<body>
    <!-- SPLASH SCREEN DI TENGAH LAYAR -->
    <div id="splash-screen">
        <div class="splash-card">
            <div class="splash-logo">
                <img src="{{ asset('images/logo_qr.png') }}" alt="Logo BBWS Brantas">
            </div>
            <div class="splash-welcome">Selamat Datang</div>
            <div class="splash-name">{{ $guest->nama }}</div>
            <div class="splash-subtitle">Terima kasih telah hadir dalam rapat ini.</div>
            <div class="splash-rapat">
                Rapat:
                <span>{{ $rapat->judul }}</span>
            </div>
            <div class="splash-loader">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
                <span>Mempersiapkan dashboard...</span>
            </div>
        </div>
    </div>

    <!-- DASHBOARD CONTENT -->
    <div class="container-main">
        <div id="dashboard-content">
            <div class="main-header">
                <div class="header-left">
                    <div id="main-logo" class="main-logo">
                        <img src="{{ asset('images/logo_qr.png') }}" alt="Logo BBWS Brantas">
                    </div>
                    <div class="header-mini">
                        <div class="header-mini-title">Dashboard tamu rapat</div>
                        <div class="header-mini-caption">
                            Informasi singkat terkait agenda dan file rapat.
                        </div>
                    </div>
                </div>
                <div class="header-right">
                    <a href="{{ route('guest.logout', ['rapat' => $rapat->id_rapat]) }}" class="btn-logout">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>

            {{-- ALERT PESAN LOGIN / ABSENSI --}}
            @if(session('info'))
                <div class="alert alert-info text-center mb-3">
                    {{ session('info') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success text-center mb-3">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Informasi Rapat -->
            <div class="card-custom">
                <h2 class="card-title-custom">
                    <i class="bi bi-calendar-event"></i>
                    Informasi Rapat
                </h2>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="bi bi-file-text"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Judul Rapat</div>
                        <div class="info-value">{{ $rapat->judul }}</div>
                    </div>
                </div>

                @if($rapat->desc)
                <div class="info-item">
                    <div class="info-icon">
                        <i class="bi bi-card-text"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Deskripsi</div>
                        <div class="info-value">{{ $rapat->desc }}</div>
                    </div>
                </div>
                @endif

                <div class="info-item">
                    <div class="info-icon">
                        <i class="bi bi-calendar3"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Tanggal</div>
                        <div class="info-value">
                            {{ \Carbon\Carbon::parse($rapat->tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                        </div>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="bi bi-clock"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Waktu</div>
                        <div class="info-value">
                            {{ \Carbon\Carbon::parse($rapat->waktu_start)->format('H:i') }}
                            @if($rapat->waktu_end)
                                - {{ \Carbon\Carbon::parse($rapat->waktu_end)->format('H:i') }} WIB
                            @else
                                WIB
                            @endif
                        </div>
                    </div>
                </div>

                @if($rapat->room)
                <div class="info-item">
                    <div class="info-icon">
                        <i class="bi bi-door-open"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Ruangan</div>
                        <div class="info-value">{{ $rapat->room->room }}</div>
                    </div>
                </div>
                @endif
            </div>

            <!-- File Rapat -->
            <div class="card-custom">
                <h2 class="card-title-custom">
                    <i class="bi bi-folder"></i>
                    File Rapat
                </h2>

                @if($rapat->files && $rapat->files->count() > 0)
                    <div class="file-list">
                        @foreach($rapat->files as $file)
                            <a href="{{ route('meetings.downloadFile', ['file' => $file->id_file]) }}"
                               target="_blank"
                               class="file-item">
                                <div class="file-icon">
                                    @if(str_contains($file->file_type, 'pdf'))
                                        <i class="bi bi-file-pdf"></i>
                                    @elseif(str_contains($file->file_type, 'image'))
                                        <i class="bi bi-file-image"></i>
                                    @elseif(str_contains($file->file_type, 'word') || str_contains($file->file_type, 'document'))
                                        <i class="bi bi-file-word"></i>
                                    @elseif(str_contains($file->file_type, 'powerpoint') || str_contains($file->file_type, 'presentation'))
                                        <i class="bi bi-file-ppt"></i>
                                    @else
                                        <i class="bi bi-file-earmark"></i>
                                    @endif
                                </div>
                                <div class="file-info">
                                    <div class="file-name">{{ $file->file_name }}</div>
                                    <div class="file-size">
                                        {{ number_format($file->file_size / 1024, 2) }} KB
                                    </div>
                                </div>
                                <div class="file-download">
                                    <i class="bi bi-download"></i>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="no-files">
                        <i class="bi bi-folder-x"></i>
                        Tidak ada file yang diunggah untuk rapat ini.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                const splashScreen = document.getElementById('splash-screen');
                const dashboardContent = document.getElementById('dashboard-content');

                splashScreen.classList.add('hidden');
                dashboardContent.classList.add('visible');
                document.body.style.overflowY = 'auto';
            }, 2800); // durasi splash (2,8 detik), bisa kamu ubah
        });
    </script>
</body>
</html>
