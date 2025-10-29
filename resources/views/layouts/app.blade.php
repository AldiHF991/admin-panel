<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel')</title>
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
        }
        .sidebar a {
            color: white;
            display: block;
            padding: 10px 20px;
            text-decoration: none;
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
    </style>
</head>
<body>

<div class="d-flex">

    <!-- Sidebar -->
    <div class="sidebar text-white">
        <div class="p-3 border-bottom border-light">
            <h4 class="fw-bold mb-0">Absensi Rapat</h4>
            <small class="text-light">Admin Dashboard</small>
        </div>

        <!-- Dashboard -->
        <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>

        <!-- Account Management -->
        <a href="{{ route('users.management') }}" class="{{ request()->is('users*') ? 'active' : '' }}">
            <i class="bi bi-people-fill me-2"></i> Account Management
        </a>

        <!-- Cabang & Ruang -->
        <a href="{{ route('branches.room') }}" class="{{ request()->is('branches*') ? 'active' : '' }}">
            <i class="bi bi-building me-2"></i> Cabang & Ruang
        </a>

        <!-- Manajemen Rapat -->
        <a href="{{ route('meetings.management') }}" class="{{ request()->is('meetings*') ? 'active' : '' }}">
            <i class="bi bi-calendar-event me-2"></i> Manajemen Rapat
        </a>

        <!-- Laporan -->
        <a href="{{ route('reports.absensi') }}" class="{{ request()->is('reports*') ? 'active' : '' }}">
            <i class="bi bi-graph-up-arrow me-2"></i> Laporan Absensi
        </a>
    </div>

    <!-- Content Area -->
    <div class="content-wrapper">

        <!-- Navbar atas -->
        <nav class="navbar navbar-light bg-white shadow-sm px-4">
            <div class="container-fluid d-flex justify-content-end">
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
</body>
</html>
