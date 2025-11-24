<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #0d6efd 0%, #6c63ff 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Poppins", sans-serif;
        }

        .login-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            padding: 2rem 2.5rem;
            width: 380px;
            transition: all 0.3s ease;
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
</body>
</html>
