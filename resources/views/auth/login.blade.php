<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" href="{{ asset('images/logo_qr.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            /* --- AURORA BACKGROUND (PUPR THEME) --- */
            background-color: #001f3f; /* Deep Blue base */
            background-image: 
                radial-gradient(50% 50% at 20% 80%, rgba(244, 160, 0, 0.3) 0%, rgba(244, 160, 0, 0) 100%), /* Yellow/Gold */
                radial-gradient(50% 50% at 80% 20%, rgba(0, 63, 135, 0.5) 0%, rgba(0, 63, 135, 0) 100%), /* PUPR Blue */
                radial-gradient(60% 60% at 50% 50%, rgba(255, 215, 0, 0.15) 0%, rgba(255, 215, 0, 0) 100%); /* Lighter Gold */
            background-size: 200% 200%;
            animation: auroraAnimation 20s ease-in-out infinite;
            /* --- END AURORA --- */

            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Poppins", sans-serif;
            position: relative; /* Diperlukan untuk pseudo-element */
            overflow: hidden; /* Mencegah highlight keluar dari body */
        }

        /* Lapisan highlight yang mengikuti kursor */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            /* Gradien radial sebagai "senter" */
            background: radial-gradient(circle 300px at var(--x) var(--y), rgba(255, 255, 255, 0.1), transparent 80%);
            will-change: background; /* Optimasi performa */
        }

        @keyframes auroraAnimation {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95); /* Sedikit transparan */
            border-radius: 15px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            padding: 2rem 2.5rem;
            width: 420px; /* Sedikit lebih lebar untuk teks panjang */
            transition: all 0.3s ease;
            z-index: 10; /* Memastikan kartu login di atas highlight */
        }

        .login-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header img {
            width: 80px;
            margin-bottom: 1rem;
            border-radius: 50%; /* Membuat logo menjadi lingkaran */
            object-fit: cover; /* Memastikan gambar tidak gepeng jika tidak persegi sempurna */
        }

        .login-header h5 {
            font-weight: 700;
            color: #003f87; /* PUPR Blue */
            font-size: 1rem;
            margin-bottom: 0.2rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .login-header h6 {
            font-weight: 600;
            color: #f4a000; /* PUPR Yellow/Gold */
            font-size: 0.85rem;
            margin-bottom: 1rem;
            text-transform: uppercase;
        }

        .login-header p {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 0;
        }

        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid #ced4da;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(0, 63, 135, 0.25);
            border-color: #003f87;
        }

        .btn-primary {
            border-radius: 10px;
            font-weight: 600;
            background-color: #003f87; /* PUPR Blue */
            border: none;
            transition: background 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #002a5c; /* Darker Blue */
        }

        .login-footer {
            font-size: 0.8rem;
            text-align: center;
            color: #6c757d;
        }

        .icon-input {
            position: relative;
        }

        .icon-input i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #003f87; /* Icon color to match theme */
        }

        .icon-input input {
            padding-left: 2.5rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <img src="{{ asset('images/icon.png') }}" alt="Logo PUPR">
            <h5>Kementerian Pekerjaan Umum</h5>
            <h6>Direktorat Jenderal Sumber Daya Air</h6>
            <p class="text-muted">Admin Panel Login</p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger text-center p-2 mb-3">
                {{ session('error') }}
            </div>
        @endif

        {{-- Menampilkan error validasi dari withErrors() --}}
        @if ($errors->any())
            <div class="alert alert-danger text-center p-2 mb-3" role="alert">
                {{-- Ambil pesan error pertama, karena kita hanya mengirim satu --}}
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3 icon-input">
                <i class="bi bi-person-fill"></i>
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="mb-3 icon-input">
                <i class="bi bi-lock-fill"></i>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">Masuk</button>
        </form>

        <div class="login-footer mt-3">
                <p class="text-muted mb-0">&copy; {{ date('Y') }} Magang UNTAG Surabaya 2025</p>
        </div>
    </div>

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="d-none">
        <div class="spinner-container">
            <div class="custom-spinner"></div>
            <p class="mt-3 font-weight-bold text-primary">Sedang Masuk...</p>
        </div>
    </div>

    <style>
        #loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(5px);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .spinner-container {
            text-align: center;
        }

        .custom-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #e0e0e0;
            border-top: 5px solid #003f87; /* PUPR Blue */
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Utility class to show overlay */
        .d-none {
            display: none !important;
        }
    </style>

    <script>
        document.addEventListener('mousemove', function(e) {
            const root = document.documentElement;
            // Mengatur posisi x dan y sebagai CSS custom properties
            // yang akan digunakan oleh pseudo-element ::before
            root.style.setProperty('--x', e.clientX + 'px');
            root.style.setProperty('--y', e.clientY + 'px');
        });

        // Show loading overlay on form submit
        document.querySelector('form').addEventListener('submit', function() {
            document.getElementById('loading-overlay').classList.remove('d-none');
        });
    </script>
</body>
</html>
