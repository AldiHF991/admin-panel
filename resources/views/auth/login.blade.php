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
            /* --- AURORA BACKGROUND --- */
            background-color: #030416; /* Warna dasar gelap untuk aurora */
            background-image: 
                radial-gradient(50% 50% at 20% 80%, rgba(108, 99, 255, 0.4) 0%, rgba(108, 99, 255, 0) 100%),
                radial-gradient(50% 50% at 80% 20%, rgba(0, 123, 255, 0.4) 0%, rgba(0, 123, 255, 0) 100%),
                radial-gradient(60% 60% at 50% 50%, rgba(40, 167, 69, 0.2) 0%, rgba(40, 167, 69, 0) 100%);
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
            background: radial-gradient(circle 300px at var(--x) var(--y), rgba(255, 255, 255, 0.15), transparent 80%);
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
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            padding: 2rem 2.5rem;
            width: 380px;
            transition: all 0.3s ease;
            z-index: 10; /* Memastikan kartu login di atas highlight */
        }

        .login-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .login-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .login-header h4 {
            font-weight: 700;
            color: #0d6efd;
        }

        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13,110,253,0.25);
            border-color: #0d6efd;
        }

        .btn-primary {
            border-radius: 10px;
            font-weight: 600;
            background-color: #0d6efd;
            border: none;
            transition: background 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #084298;
        }

        .login-footer {
            font-size: 0.9rem;
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
            color: #6c757d;
        }

        .icon-input input {
            padding-left: 2.2rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h4>Admin Panel</h4>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Masuk untuk melanjutkan</p>
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

    <script>
        document.addEventListener('mousemove', function(e) {
            const root = document.documentElement;
            // Mengatur posisi x dan y sebagai CSS custom properties
            // yang akan digunakan oleh pseudo-element ::before
            root.style.setProperty('--x', e.clientX + 'px');
            root.style.setProperty('--y', e.clientY + 'px');
        });
    </script>
</body>
</html>
