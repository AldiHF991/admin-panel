<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Panel' }} | Simrapel Brantas</title>
    <link rel="icon" href="{{ asset('images/logo_qr.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (localStorage.getItem('sidebarCollapsed') === 'true') && document.documentElement.classList.add('sidebar-collapsed');
    </script>
    <style>
        html, body {
            height: 100%;
            overflow-x: hidden; /* cegah geser horizontal */
        }

        body {
            background-color: #f8f9fa;
            margin: 0;
        }

        .sidebar {
            width: 250px;
            min-height: 100vh;
            background-color: #0d6efd;
            position: relative; /* untuk toggle */
            display: flex;
            flex-direction: column;
            z-index: 20; /* Ensure sidebar is above content */
        }

        .sidebar a {
            color: white;
            display: block;
            padding: 10px 20px;
            text-decoration: none;
            white-space: nowrap;
        }
        .sidebar a:hover {
            background-color: #0b5ed7;
        }
        .sidebar .active {
            background-color: #0a58ca;
        }

        /* BAGIAN KANAN: navbar (atas), main scroll, footer (bawah) */
        .content-wrapper {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;      /* tinggi selalu = viewport */
            overflow: hidden;   /* jangan biarkan scroll di wrapper, hanya di main */
        }

        main {
            flex: 1 1 auto;     /* ambil ruang di tengah */
            display: flex;
            flex-direction: column;
            overflow-y: auto;   /* YANG DISCROLL HANYA MAIN */
        }

        /* Transisi Halus */
        .sidebar, .content-wrapper {
            transition: all 0.3s ease-in-out;
        }

        /* Sidebar collapsed */
        .sidebar-collapsed .sidebar, .sidebar.collapsed {
            width: 80px;
        }

        .sidebar-collapsed .sidebar .sidebar-brand-text,
        .sidebar-collapsed .sidebar .sidebar-link-text,
        .sidebar.collapsed .sidebar-brand-text,
        .sidebar.collapsed .sidebar-link-text {
            display: none;
        }

        .sidebar-collapsed .sidebar .sidebar-brand,
        .sidebar.collapsed .sidebar-brand {
            justify-content: center;
        }

        .sidebar-collapsed .sidebar a i,
        .sidebar.collapsed a i {
            font-size: 1.5rem;
        }

        /* Tombol Toggle Sidebar Baru */
        .sidebar-toggle {
            position: absolute;
            top: 50%;
            right: -15px;
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
            z-index: 21; /* Ensure toggle is above sidebar */
        }
        .sidebar-toggle:hover {
            background-color: #0a58ca;
            transform: translateY(-50%) scale(1.1);
        }
        .sidebar-toggle i {
            transition: transform 0.3s ease;
        }
        .sidebar-collapsed .sidebar .sidebar-toggle i,
        .sidebar.collapsed .sidebar-toggle i {
            transform: rotate(180deg);
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -250px;
                height: 100vh;
                z-index: 1050;
                transition: left 0.3s ease-in-out;
            }
            .sidebar.mobile-open {
                left: 0;
            }
            .sidebar-toggle {
                display: none; /* Hide desktop toggle on mobile */
            }
            .mobile-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(0,0,0,0.5);
                z-index: 1040;
            }
            .mobile-overlay.show {
                display: block;
            }
        }
    </style>
</head>
<body>

<body>

<div class="mobile-overlay" id="mobileOverlay"></div>

<div class="d-flex">

    <!-- Sidebar -->
    <div class="sidebar text-white" id="sidebar">
        <div class="p-3 border-bottom border-light d-flex align-items-center sidebar-brand">
            <img src="{{ asset('images/logo1.png') }}" alt="BBWS Brantas Logo" style="height: 40px;" class="me-3">
            <div>
                <h4 class="fw-bold mb-0 sidebar-brand-text">Simrapel Brantas</h4>
                <small class="text-light sidebar-brand-text">Admin Dashboard</small>
            </div>
        </div>

        @if(Auth::user()->id_role == 2)
            <!-- PIC Sidebar -->
            <a href="{{ route('pic.dashboard') }}" class="{{ request()->routeIs('pic.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i> <span class="sidebar-link-text">Dashboard</span>
            </a>
            <a href="{{ route('pic.meetings.index') }}" class="{{ request()->routeIs('pic.meetings.index') ? 'active' : '' }}">
                <i class="bi bi-list-ul me-2"></i> <span class="sidebar-link-text">List Rapat</span>
            </a>
            <a href="{{ route('pic.meetings.create') }}" class="{{ request()->routeIs('pic.meetings.create') ? 'active' : '' }}">
                <i class="bi bi-plus-square me-2"></i> <span class="sidebar-link-text">Ajukan Rapat</span>
            </a>
        @else
            <!-- Admin Sidebar -->
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i> <span class="sidebar-link-text">Dashboard</span>
            </a>
            <a href="{{ route('userManagement') }}" class="{{ request()->routeIs('userManagement') ? 'active' : '' }}">
                <i class="bi bi-people-fill me-2"></i> <span class="sidebar-link-text">Account Management</span>
            </a>
            <a href="{{ route('branch') }}" class="{{ request()->routeIs('branch*') ? 'active' : '' }}">
                <i class="bi bi-building me-2"></i> <span class="sidebar-link-text">Cabang & Ruang</span>
            </a>
            <a href="{{ route('meetings.incoming') }}" class="{{ request()->routeIs('meetings.incoming') ? 'active' : '' }}">
                <i class="bi bi-inbox-fill me-2"></i> <span class="sidebar-link-text">Permintaan Rapat</span>
            </a>
            <a href="{{ route('meetings.index') }}" class="{{ request()->routeIs('meetings.index') || request()->routeIs('meetings.create') || request()->routeIs('meetings.edit') ? 'active' : '' }}">
                <i class="bi bi-calendar-event me-2"></i> <span class="sidebar-link-text">Manajemen Rapat</span>
            </a>
            <a href="{{ route('guests.index') }}" class="{{ request()->routeIs('guests.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge me-2"></i> <span class="sidebar-link-text">Guest Management</span>
            </a>
        @endif

        <!-- Logout Button -->
        <div class="mt-auto p-3 mb-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger w-100 d-flex align-items-center justify-content-center">
                    <i class="bi bi-box-arrow-right me-2"></i> <span class="sidebar-link-text">Logout</span>
                </button>
            </form>
        </div>

        <!-- Tombol Toggle Sidebar -->
        <div id="sidebarToggle" class="sidebar-toggle">
            <i class="bi bi-chevron-left"></i>
        </div>
    </div>

    <!-- Content Area -->
    <div class="content-wrapper">

        <!-- Navbar atas (tetap di atas, tidak ikut scroll main) -->
        <nav class="navbar navbar-light bg-white shadow-sm px-4">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <!-- Mobile Toggle Button -->
                <button class="btn btn-link d-md-none me-3 text-dark p-0" id="mobileSidebarToggle">
                    <i class="bi bi-list fs-1"></i>
                </button>

                <div class="d-flex align-items-center ms-auto">
                @if(Auth::check() && (Auth::user()->id_role == 1 || Auth::user()->id_role == 2))
                    <div class="dropdown me-3">
                        <a href="#" class="text-decoration-none text-dark position-relative" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell fs-5"></i>
                            @if(Auth::user()->unreadNotifications->count() > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                    {{ Auth::user()->unreadNotifications->count() }}
                                    <span class="visually-hidden">unread messages</span>
                                </span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="notificationDropdown" style="width: 320px; max-height: 400px; overflow-y: auto;">
                            <li><h6 class="dropdown-header bg-primary text-white mb-2">Notifikasi Baru</h6></li>
                            @forelse(Auth::user()->notifications()->latest()->take(10)->get() as $notification)
                                <li>
                                    <div class="dropdown-item py-2 d-flex align-items-start {{ $notification->read_at ? 'opacity-75' : '' }}" style="white-space: normal; cursor: default; {{ $notification->read_at ? 'background-color: #f8f9fa;' : '' }}">
                                        <a class="flex-grow-1 text-decoration-none text-dark" href="{{ route('notifications.read', $notification->id) }}">
                                            <div class="d-flex align-items-start">
                                                <div class="flex-shrink-0 me-2">
                                                    <i class="bi bi-calendar-check {{ $notification->read_at ? 'text-secondary' : 'text-primary' }}"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 small {{ $notification->read_at ? 'fw-normal' : 'fw-bold' }}">{{ $notification->data['title'] ?? 'Notifikasi' }}</h6>
                                                    <p class="mb-1 small text-muted text-wrap">{{ $notification->data['message'] ?? '' }}</p>
                                                    @if(isset($notification->data['note']))
                                                        <p class="mb-1 small text-danger fw-bold">Alasan: {{ $notification->data['note'] }}</p>
                                                    @endif
                                                    <small class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}</small>
                                                </div>
                                            </div>
                                        </a>
                                        {{-- Tombol Aksi HANYA untuk Admin (Role 1) --}}
                                        @if(Auth::user()->id_role == 1 && isset($notification->data['meeting_id']) && is_null($notification->read_at))
                                            <div class="d-flex flex-column ms-2 gap-1">
                                                <form action="{{ route('meetings.accept', $notification->data['meeting_id']) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success p-0 d-flex align-items-center justify-content-center rounded-circle shadow-sm" style="width: 24px; height: 24px;" title="Terima" onclick="return confirm('Apakah Anda yakin ingin menerima rapat ini?')">
                                                        <i class="bi bi-check"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-danger p-0 d-flex align-items-center justify-content-center rounded-circle shadow-sm" style="width: 24px; height: 24px;" title="Tolak" onclick="openRejectionModal('{{ route('meetings.reject', $notification->data['meeting_id']) }}')">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            @empty
                                <li><div class="dropdown-item text-center text-muted small py-3">Tidak ada notifikasi</div></li>
                            @endforelse
                        </ul>
                    </div>
                @endif
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
            </div>
        </nav>

        <!-- Page Content: HANYA BAGIAN INI YANG DISCROLL -->
        <main class="p-4 d-flex flex-column">
            <div class="flex-grow-1">
                @yield('content')
            </div>

            <!-- Footer: tetap menempel di bawah -->
            <footer class="text-end py-3 border-top text-secondary small w-100 mt-auto" style="text-align: right !important;">
                © {{ date('Y') }} Sistem Absensi Rapat — All Rights Reserved.
            </footer>
        </main>
    </div>

</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectionModal" tabindex="-1" aria-labelledby="rejectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectionForm" method="POST" action="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectionModalLabel">Tolak Pengajuan Rapat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejection_note" class="form-label">Alasan Penolakan</label>
                        <textarea class="form-control" id="rejection_note" name="rejection_note" rows="3" required placeholder="Contoh: Ruangan tidak tersedia, jadwal bentrok, dll."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Rapat</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-info text-dark">
            <i class="bi bi-bell-fill me-2"></i>
            <strong class="me-auto" id="toast-title">Notifikasi Baru</strong>
            <small>Baru saja</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <p class="mb-1" id="toast-message"></p>
            <p class="mb-0 text-danger fw-bold small" id="toast-note" style="display: none;"></p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
<script>
    (function() {
        const htmlEl = document.documentElement;
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const isCollapsed = () => localStorage.getItem('sidebarCollapsed') === 'true';

        const syncSidebarState = () => {
            if (isCollapsed()) {
                htmlEl.classList.remove('sidebar-collapsed');
                sidebar.classList.add('collapsed');
            }
        };

        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });

        setTimeout(syncSidebarState, 0);
        setTimeout(syncSidebarState, 0);

        // Mobile Sidebar Logic
        const mobileToggle = document.getElementById('mobileSidebarToggle');
        const mobileOverlay = document.getElementById('mobileOverlay');

        if (mobileToggle) {
            mobileToggle.addEventListener('click', function() {
                sidebar.classList.toggle('mobile-open');
                mobileOverlay.classList.toggle('show');
            });
        }

        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', function() {
                sidebar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('show');
            });
        }
    })();

    function openRejectionModal(url) {
        const form = document.getElementById('rejectionForm');
        form.action = url;
        const modal = new bootstrap.Modal(document.getElementById('rejectionModal'));
        modal.show();
    }

    // Auto-mark notifications as read for PIC when dropdown is opened
    @if(Auth::check() && Auth::user()->id_role == 2)
        const notificationDropdown = document.getElementById('notificationDropdown');
        if (notificationDropdown) {
            notificationDropdown.addEventListener('show.bs.dropdown', function () {
                const badge = this.querySelector('.badge');
                if (badge) {
                    // Hide badge immediately
                    badge.style.display = 'none';
                    
                    // Send AJAX request to mark all as read
                    fetch('{{ route("notifications.markAllRead") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    }).then(response => {
                        if (!response.ok) console.error('Failed to mark notifications as read');
                    }).catch(error => console.error('Error:', error));
                }
            });
        }
    @endif

    // Real-time Notifications
    @if(Auth::check())
        document.addEventListener('DOMContentLoaded', function() {
            const userId = "{{ Auth::user()->id_user }}";
            console.log('Listening for notifications on channel: App.Models.User.' + userId);
            
            if (window.Echo) {
                // Preload notification sound
                const notificationSound = new Audio("{{ asset('sounds/notif_sound.mp3') }}");
                const toastElement = document.getElementById('liveToast');
                const toast = new bootstrap.Toast(toastElement);

                window.Echo.private('App.Models.User.' + userId)
                    .notification((notification) => {
                        console.log('Notification received:', notification);
                        
                        // Play Sound
                        notificationSound.play().catch(error => console.log('Audio play failed:', error));
                        
                        // Show Toast
                        document.getElementById('toast-title').innerText = notification.title;
                        document.getElementById('toast-message').innerText = notification.message;
                        const toastNote = document.getElementById('toast-note');
                        if (notification.note) {
                            toastNote.innerText = 'Alasan: ' + notification.note;
                            toastNote.style.display = 'block';
                        } else {
                            toastNote.style.display = 'none';
                        }
                        toast.show();

                        // Update Badge
                        const badge = document.querySelector('#notificationDropdown .badge');
                        if (badge) {
                            if (badge.style.display === 'none') {
                                badge.style.display = 'block';
                                badge.innerText = 1;
                            } else {
                                badge.innerText = parseInt(badge.innerText) + 1;
                            }
                        } else {
                            // Create badge if it doesn't exist
                            const dropdownLink = document.getElementById('notificationDropdown');
                            const newBadge = document.createElement('span');
                            newBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                            newBadge.style.fontSize = '0.6rem';
                            newBadge.innerText = '1';
                            newBadge.innerHTML += '<span class="visually-hidden">unread messages</span>';
                            dropdownLink.appendChild(newBadge);
                        }

                        // Add to Dropdown List
                        const dropdownMenu = document.querySelector('.dropdown-menu[aria-labelledby="notificationDropdown"]');
                        const header = dropdownMenu.querySelector('.dropdown-header');
                        
                        // Remove "Tidak ada notifikasi" if exists
                        const emptyItem = dropdownMenu.querySelector('.text-center.text-muted');
                        if (emptyItem) {
                            emptyItem.closest('li').remove();
                        }

                        const newItem = document.createElement('li');
                        let actionButtons = '';
                        
                        // Add action buttons for Admin if meeting_id exists
                        @if(Auth::user()->id_role == 1)
                            if (notification.meeting_id) {
                                actionButtons = `
                                    <div class="d-flex flex-column ms-2 gap-1">
                                        <form action="/meetings/${notification.meeting_id}/accept" method="POST">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <button type="submit" class="btn btn-sm btn-success p-0 d-flex align-items-center justify-content-center rounded-circle shadow-sm" style="width: 24px; height: 24px;" title="Terima" onclick="return confirm('Apakah Anda yakin ingin menerima rapat ini?')">
                                                <i class="bi bi-check"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger p-0 d-flex align-items-center justify-content-center rounded-circle shadow-sm" style="width: 24px; height: 24px;" title="Tolak" onclick="openRejectionModal('/meetings/${notification.meeting_id}/reject')">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        @endif

                        let rejectionNote = '';
                        if (notification.note) {
                            rejectionNote = `<p class="mb-1 small text-danger fw-bold">Alasan: ${notification.note}</p>`;
                        }

                        newItem.innerHTML = `
                            <div class="dropdown-item py-2 d-flex align-items-start" style="white-space: normal; cursor: default;">
                                <a class="flex-grow-1 text-decoration-none text-dark" href="/notifications/${notification.id}/read">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-shrink-0 me-2">
                                            <i class="bi bi-calendar-check text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 small fw-bold">${notification.title}</h6>
                                            <p class="mb-1 small text-muted text-wrap">${notification.message}</p>
                                            ${rejectionNote}
                                            <small class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-clock me-1"></i>Baru saja</small>
                                        </div>
                                    </div>
                                </a>
                                ${actionButtons}
                            </div>
                        `;
                        
                        // Insert after header
                        header.parentElement.after(newItem);
                        
                        // Insert divider
                        const divider = document.createElement('li');
                        divider.innerHTML = '<hr class="dropdown-divider">';
                        newItem.after(divider);
                    });
            } else {
                console.error('Laravel Echo not loaded');
            }
        });
    @endif
</script>
</body>
</html>
