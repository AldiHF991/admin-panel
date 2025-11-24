<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Panel' }} | BBWS Brantas</title>
    <link rel="icon" href="{{ asset('images/logo_qr.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            width: 250px;
            min-height: 100vh;
            background-color: #0d6efd;
            position: relative; /* Diperlukan untuk positioning toggle */
        }

        .sidebar a {
            color: white;
            display: block;
            padding: 10px 20px;
            text-decoration: none;
            white-space: nowrap; /* Mencegah teks turun baris */
        }
        .sidebar a:hover {
            background-color: #0b5ed7;
        }
        .sidebar .active {
            background-color: #0a58ca;
        }
        .content-wrapper {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        main {
            flex-grow: 1;
            overflow-y: auto;
        }

        /* Transisi Halus */
        .sidebar, .content-wrapper {
            transition: all 0.3s ease-in-out;
        }

        /* State Sidebar saat diperkecil (collapsed) */
        .sidebar.collapsed {
            width: 80px;
        }

        .sidebar.collapsed .sidebar-brand-text,
        .sidebar.collapsed .sidebar-link-text {
            display: none;
        }

        .sidebar.collapsed .sidebar-brand {
            justify-content: center;
        }

        .sidebar.collapsed a i {
            font-size: 1.5rem; /* Perbesar ikon saat sidebar kecil */
        }
        /* Tombol Toggle Sidebar Baru */
        .sidebar-toggle {
            position: absolute;
            top: 50%;
            right: -15px; /* Menonjol keluar dari sidebar */
            transform: translateY(-50%);
            width: 30px;
            height: 30px;
            background-color: #0d6efd;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
        }
        .sidebar-toggle:hover {
            background-color: #0a58ca;
            transform: translateY(-50%) scale(1.1);
        }
        .sidebar-toggle i {
            transition: transform 0.3s ease;
        }
        .sidebar.collapsed .sidebar-toggle i {
            transform: rotate(180deg);
        }
    </style>
</head>
<body>

<div class="d-flex">

    <!-- Sidebar -->
    <div class="sidebar text-white" id="sidebar">
        <div class="p-3 border-bottom border-light d-flex align-items-center sidebar-brand">
            <img src="{{ asset('images/logo1.png') }}" alt="BBWS Brantas Logo" style="height: 40px;" class="me-3">
            <div>
                <h4 class="fw-bold mb-0 sidebar-brand-text">BBWS Brantas</h4>
                <small class="text-light sidebar-brand-text">Admin Dashboard</small>
            </div>
        </div>

        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2 me-2"></i> <span class="sidebar-link-text">Dashboard</span>
        </a>

        <!-- Account Management -->
        <a href="{{ route('userManagement') }}" class="{{ request()->routeIs('userManagement') ? 'active' : '' }}">
            <i class="bi bi-people-fill me-2"></i> <span class="sidebar-link-text">Account Management</span>
        </a>

        <!-- Cabang & Ruang -->
        <a href="{{ route('branch') }}" class="{{ request()->routeIs('branch*') ? 'active' : '' }}">
            <i class="bi bi-building me-2"></i> <span class="sidebar-link-text">Cabang & Ruang</span>
        </a>

        <!-- Manajemen Rapat -->
        <a href="{{ route('meetings.index') }}" class="{{ request()->routeIs('meetings.*') ? 'active' : '' }}">
            <i class="bi bi-calendar-event me-2"></i> <span class="sidebar-link-text">Manajemen Rapat</span>
        </a>

        <!-- Tombol Toggle Sidebar -->
        <div id="sidebarToggle" class="sidebar-toggle">
            <i class="bi bi-chevron-left"></i>
        </div>
    </div>

    <!-- Content Area -->
    <div class="content-wrapper">

        <!-- Navbar atas -->
        <nav class="navbar navbar-light bg-white shadow-sm px-4">
            <div class="container-fluid d-flex justify-content-end"> <!-- Tombol hamburger dihapus dari sini -->
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle"
                       id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="me-2 fw-semibold">{{ Auth::user()->name ?? 'Admin' }}</span>
                        <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center"
                             style="width: 36px; height: 36px;">
                            {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownUser">
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="p-4">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="text-center py-3 border-top text-secondary small">
            © {{ date('Y') }} Sistem Absensi Rapat — All Rights Reserved.
        </footer>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');

        // Cek status sidebar dari localStorage
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
        }

        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');
            // Simpan status ke localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    });
</script>
</body>
</html>
