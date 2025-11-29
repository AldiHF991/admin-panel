<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Guest Mode</title>
    <link rel="icon" href="{{ asset('images/logo_qr.png') }}" type="image/png"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            /* Warna Dominan Logo Kementerian PU */
            --pu-blue-dark: #001A33;    /* Biru sangat gelap untuk dasar background */
            --pu-blue-main: #003366;    /* Biru utama dari logo PU */
            --pu-blue-light: #00509E;   /* Biru lebih terang untuk aksen dan gradien */
            --pu-yellow: #F4C430;       /* Kuning emas dari logo PU */
        }

        body {
            /* --- PU THEMED AURORA BACKGROUND --- */
            background-color: var(--pu-blue-dark);
            background-image: 
                /* Gradien cahaya Kuning Emas (lebih dominan) */
                radial-gradient(ellipse 40% 50% at 20% 80%, rgba(244, 196, 48, 0.25) 0%, rgba(244, 196, 48, 0) 100%),
                /* Gradien cahaya Biru Terang (lebih dominan) */
                radial-gradient(ellipse 40% 50% at 80% 20%, rgba(0, 80, 158, 0.5) 0%, rgba(0, 80, 158, 0) 100%),
                /* Gradien cahaya Biru Utama di tengah */
                radial-gradient(ellipse 50% 60% at 50% 50%, rgba(0, 51, 102, 0.4) 0%, rgba(0, 51, 102, 0) 100%);
            background-size: 200% 200%;
            animation: auroraAnimation 15s ease-in-out infinite;
            /* --- END AURORA --- */

            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Poppins", sans-serif;
            position: relative;
            overflow: hidden;
        }

        /* Highlight cursor dibuat sedikit keemasan agar elegan */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle 350px at var(--x) var(--y), rgba(255, 255, 255, 0.08), transparent 80%);
            will-change: background;
            pointer-events: none; /* Agar tidak mengganggu klik */
        }

        @keyframes auroraAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 15px;
            /* Shadow dengan sedikit nuansa biru tua */
            box-shadow: 0 10px 30px rgba(0, 26, 51, 0.5); 
            padding: 2.5rem 2.5rem;
            width: 380px;
            transition: all 0.3s ease;
            z-index: 10;
            border-top: 5px solid var(--pu-yellow); /* Aksen garis emas di atas kartu */
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 26, 51, 0.6);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        /* Style untuk logo di atas judul */
        .login-logo {
            max-width: 90px; /* Ukuran logo yang disesuaikan */
            height: auto;
            display: block;
            margin: 0 auto 1.5rem auto; /* Otomatis di tengah dan memberi jarak bawah */
        }

        .login-header h4 {
            font-weight: 800;
            color: var(--pu-blue-main); /* Judul menggunakan Biru PU */
            letter-spacing: 0.5px;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #ced4da;
            transition: all 0.3s;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(0, 51, 102, 0.15); /* Shadow biru */
            border-color: var(--pu-blue-main);
        }

        /* Tombol Utama - Biru PU */
        .btn-primary {
            border-radius: 8px;
            font-weight: 600;
            /* Menggunakan gradien biru yang sesuai tema */
            background-image: linear-gradient(45deg, var(--pu-blue-light), var(--pu-blue-main));
            border: none;
            transition: all 0.3s ease;
            padding-top: 10px;
            padding-bottom: 10px;
            background-size: 150% auto; /* Untuk efek hover */
        }

        .btn-primary:hover {
            background-position: right center; /* Menggeser gradien saat hover */
            box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3);
        }

        .login-footer {
            font-size: 0.85rem;
            text-align: center;
            color: #6c757d;
            margin-top: 1.5rem;
        }

        .icon-input {
            position: relative;
        }

        /* Ikon input diberi warna Emas PU agar kontras dan elegan */
        .icon-input i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--pu-yellow); /* Menggunakan warna kuning emas PU */
            font-size: 1.1rem;
            z-index: 5;
        }

        .icon-input input {
            padding-left: 3rem; /* Memberi ruang untuk ikon */
        }
        
        /* Custom Alert Style */
        .alert-danger {
            background-color: #fff2f2;
            border-color: #ffcccc;
            color: #cc0000;
            font-size: 0.9rem;
            border-radius: 8px;
        }

        /* --- RESPONSIVE DESIGN UNTUK MOBILE --- */
        @media (max-width: 576px) {
            .login-card {
                width: 90%; /* Lebar kartu menjadi 90% dari layar */
                padding: 2rem 1.5rem; /* Padding dikurangi agar konten tidak terlalu sempit */
                box-shadow: 0 8px 25px rgba(0, 26, 51, 0.4);
            }

            .login-header h4 {
                font-size: 1.6rem; /* Ukuran judul disesuaikan */
            }

            .login-header p {
                font-size: 0.85rem !important; /* Ukuran sub-judul disesuaikan */
            }
        }

        /* --- STYLE UNTUK QR SCANNER POPUP --- */
        #qr-scanner-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.85);
            display: none; /* Sembunyi secara default */
            align-items: center;
            justify-content: center;
            z-index: 1050;
            flex-direction: column;
        }

        #qr-reader {
            width: 90vw;
            max-width: 500px;
            background: #111;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid var(--pu-yellow);
        }

        #close-scanner-btn {
            margin-top: 20px;
            background-color: #fff;
            color: #333;
            font-weight: 600;
            border: none;
        }

        /* Animasi garis pemindai */
        #qr-reader-results {
            color: white;
            margin-top: 10px;
            font-size: 0.9rem;
        }

        /* Menyembunyikan beberapa elemen UI default dari library */
        #qr-reader__dashboard_section_csr, #qr-reader__dashboard_section_fsr {
            display: none !important;
        }
        
        .welcome-text {
            font-size: 1rem;
        }

    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <img src="{{ asset('images/logo_qr.png') }}" alt="Logo BBWS Brantas" class="login-logo" style="max-width: 75px; margin-bottom: 1rem;">
            {{-- Logika untuk menampilkan judul rapat atau judul default --}}
            @if(isset($rapat))
                <p class="text-muted mb-2 welcome-text">Selamat Datang di Rapat:</p>
                <h4 style="font-size: 1.4rem; line-height: 1.4; font-weight: 700;">{{ $rapat->judul }}</h4>
                <p class="text-muted mt-2" style="font-size: 0.9rem;">Silakan isi formulir kehadiran di bawah ini.</p>
            @else
                <h4>Selamat Datang</h4>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Sistem Manajemen Rapat BBWS Brantas</p>
            @endif
        </div>

        @if(session('error'))
            <div class="alert alert-danger text-center p-2 mb-3">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success text-center p-2 mb-3">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger text-center p-2 mb-3">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Tombol di luar form untuk memicu popup -->
        <button type="button" class="btn btn-primary w-100 mt-3" data-bs-toggle="modal" data-bs-target="#guest-form-modal">
            <i class="bi bi-box-arrow-in-right me-2"></i>Masuk Rapat
        </button>

        <div class="login-footer">
            <p class="mb-0">&copy; {{ date('Y') }} Kementerian Pekerjaan Umum</p>
            <p class="text-muted small">Magang UNTAG Surabaya 2025</p>
        </div>
    </div>
    
    <!-- Modal untuk Form Data Diri Tamu -->
    <div class="modal fade" id="guest-form-modal" tabindex="-1" aria-labelledby="guestFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom: 2px solid var(--pu-yellow);">
                    <h5 class="modal-title" id="guestFormModalLabel" style="color: var(--pu-blue-main); font-weight: 600;">Formulir Kehadiran Tamu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Sesuaikan action form --}}
                    @if(isset($rapat))
                        <form id="guest-form" action="{{ route('meetings.storeGuest', ['rapat' => $rapat->id_rapat]) }}" method="POST">
                    @endif
                        @csrf

                        {{-- HIDDEN INPUT UNTUK DEVICE TOKEN --}}
                        <input type="hidden" id="device_token" name="device_token">

                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label for="asal_instansi" class="form-label">Asal Instansi / Unit Kerja <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="asal_instansi" name="asal_instansi" required>
                        </div>
                        <div class="mb-3">
                            <label for="jabatan" class="form-label">Jabatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="jabatan" name="jabatan" required>
                        </div>
                        <div class="mb-3">
                            <label for="nomor" class="form-label">Nomor WhatsApp (Opsional)</label>
                            <input type="tel" class="form-control" id="nomor" name="nomor" placeholder="Contoh: 081234567890">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" form="guest-form" class="btn btn-primary">Kirim</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk QR Code Halaman -->
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <script>
        document.addEventListener('mousemove', function(e) {
            const root = document.documentElement;
            root.style.setProperty('--x', e.clientX + 'px');
            root.style.setProperty('--y', e.clientY + 'px');
        });

        // ===== DEVICE TOKEN GUEST (TIDAK MENGGANGGU KODE YANG ADA) =====
        function getOrCreateGuestDeviceToken() {
            try {
                let token = localStorage.getItem('guest_device_token');

                if (!token) {
                    if (window.crypto && window.crypto.randomUUID) {
                        token = window.crypto.randomUUID();
                    } else {
                        // Fallback kalau browser sangat tua
                        token = 'dev-' + Math.random().toString(36).substring(2) + Date.now();
                    }
                    localStorage.setItem('guest_device_token', token);
                }

                return token;
            } catch (e) {
                // Jika localStorage tidak bisa diakses (mode private ketat), tetap generate token sementara
                return 'dev-' + Math.random().toString(36).substring(2) + Date.now();
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const token = getOrCreateGuestDeviceToken();
            const input = document.getElementById('device_token');
            if (input) {
                input.value = token;
            }
            // console.log('guest device_token:', token);
        });
        // =================================================================
    </script>
</body>
</html>
