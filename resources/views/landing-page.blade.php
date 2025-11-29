<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Rapat | BBWS Brantas</title>
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
            margin: 0;
            min-height: 100vh;
            font-family: "Poppins", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(14,165,233,.25), transparent 60%),
                radial-gradient(circle at bottom right, rgba(244,196,48,.18), transparent 55%),
                #020617;
            color: #e5e7eb;

            /* Biar gradasi bisa "jalan" */
            background-size: 180% 180%;

            /* ANIMASI HALAMAN SAAT PERTAMA LOAD + GERAK GRADIENT */
            opacity: 0;
            animation:
                bodyFadeIn 0.7s ease-out forwards,
                bgMove 26s ease-in-out infinite alternate;
        }

        /* ANIMASI KEYFRAMES */
        @keyframes bodyFadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        /* Animasi background gradient pelan bergerak */
        @keyframes bgMove {
            0% {
                background-position: 0% 0%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 100%;
            }
        }

        @keyframes fadeInDownSoft {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUpSoft {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .landing-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 1;
        }

        /* NAVBAR */
        .navbar-custom {
            background: transparent;
            padding: 1rem 0;

            /* Animasi navbar turun lembut */
            opacity: 0;
            animation: fadeInDownSoft 0.6s ease-out 0.15s forwards;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: .75rem;
            font-weight: 600;
            color: #e5e7eb !important;
            font-size: 1rem;
        }

        .navbar-brand-logo {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(15,23,42,0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(148,163,184,0.5);
        }

        .navbar-brand-logo img {
            width: 26px;
            height: auto;
        }

        .nav-badge {
            font-size: 0.7rem;
            padding: 0.1rem .5rem;
            border-radius: 999px;
            background: rgba(15,118,110,0.15);
            color: #6ee7b7;
            border: 1px solid rgba(45,212,191,0.3);
        }

        .nav-buttons .btn {
            font-size: 0.85rem;
            border-radius: .8rem;
        }

        .btn-admin {
            background: linear-gradient(135deg, var(--pu-yellow), #fde68a);
            border: none;
            color: #1f2933;
            font-weight: 600;
        }

        .btn-admin:hover {
            opacity: 0.9;
        }

        .btn-pic {
            border-radius: .8rem;
            border: 1px solid rgba(148,163,184,0.6);
            background: rgba(15,23,42,0.5);
            color: #e5e7eb;
        }

        .btn-pic:hover {
            background: rgba(15,23,42,0.8);
            color: #ffffff;
        }

        /* HERO */
        .hero-section {
            flex: 1;
            display: flex;
            align-items: center;
            padding: 1.5rem 0 2.5rem;
        }

        .hero-title {
            font-size: clamp(1.9rem, 3vw, 2.4rem);
            font-weight: 700;
            line-height: 1.2;
            color: #f9fafb;
        }

        .hero-title span {
            background: linear-gradient(135deg, #facc15, #0ea5e9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: 0.95rem;
            color: #cbd5f5;
            max-width: 480px;
            margin-top: .75rem;
            margin-bottom: 1.25rem;
        }

        .hero-pills {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-bottom: 1.5rem;
        }

        .hero-pill {
            font-size: 0.73rem;
            padding: 0.25rem 0.7rem;
            border-radius: 999px;
            border: 1px solid rgba(148,163,184,0.4);
            color: #e5e7eb;
            background: rgba(15,23,42,0.65);
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
            align-items: center;
            margin-bottom: 1.2rem;
        }

        .hero-demo {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
            color: #9ca3af;
        }

        .hero-demo i {
            color: #22c55e;
        }

        /* FEATURE CARDS */
        .feature-card {
            background: rgba(15,23,42,0.9);
            border-radius: 1.1rem;
            padding: 1.25rem 1.1rem;
            border: 1px solid rgba(148,163,184,0.4);
            color: #e5e7eb;
            box-shadow: 0 18px 40px rgba(15,23,42,0.9);
            position: relative;
            overflow: hidden;
            transition: transform 0.25s ease-out, box-shadow 0.25s ease-out;
            transform-style: preserve-3d;
        }

        .feature-card.parallax-active {
            box-shadow: 0 24px 60px rgba(15,23,42,1);
        }

        .feature-chip {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #9ca3af;
            margin-bottom: 0.35rem;
        }

        .feature-main {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .feature-desc {
            font-size: 0.8rem;
            color: #9ca3af;
            margin-bottom: 0.8rem;
        }

        .feature-list {
            list-style: none;
            padding-left: 0;
            margin: 0;
            font-size: 0.8rem;
        }

        .feature-list li {
            display: flex;
            align-items: center;
            gap: .35rem;
            margin-bottom: .35rem;
        }

        .feature-list i {
            font-size: .9rem;
            color: #22c55e;
        }

        .feature-badge-corner {
            position: absolute;
            right: -42px;
            top: -42px;
            width: 110px;
            height: 110px;
            border-radius: 999px;
            background: radial-gradient(circle at 30% 30%, rgba(250,204,21,0.9), transparent 70%);
            opacity: .12;
        }

        .feature-mini {
            margin-top: 1rem;
            display: grid;
            grid-template-columns: repeat(2, minmax(0,1fr));
            gap: .85rem;
        }

        .mini-card {
            background: rgba(15,23,42,0.8);
            border-radius: .9rem;
            padding: 0.7rem .7rem;
            border: 1px solid rgba(55,65,81,0.7);
            font-size: 0.76rem;
            color: #e5e7eb;
        }

        .mini-label {
            font-size: 0.7rem;
            color: #9ca3af;
        }

        .mini-value {
            font-weight: 500;
        }

        /* SECTION CARA TAMU MASUK */
        .section-steps {
            margin-top: 2rem;
        }

        .steps-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #e5e7eb;
            margin-bottom: 0.5rem;
        }

        .steps-caption {
            font-size: 0.78rem;
            color: #9ca3af;
            margin-bottom: 1rem;
        }

        .step-list {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
        }

        .step-item {
            flex: 1 1 160px;
            font-size: 0.8rem;
            color: #cbd5f5;
            background: rgba(15,23,42,0.85);
            border-radius: .9rem;
            padding: 0.7rem .75rem;
            border: 1px solid rgba(55,65,81,0.7);
            display: flex;
            gap: .6rem;
        }

        .step-number {
            width: 24px;
            height: 24px;
            border-radius: 999px;
            background: rgba(37,99,235,0.2);
            border: 1px solid rgba(59,130,246,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            color: #bfdbfe;
            flex-shrink: 0;
        }

        .step-text {
            font-size: 0.78rem;
        }

        .step-highlight {
            color: #e5e7eb;
            font-weight: 500;
        }

        /* ANIMASI HERO KIRI & KARTU KANAN SAAT LOAD */
        .hero-left-animate {
            opacity: 0;
            animation: fadeInUpSoft 0.7s ease-out 0.25s forwards;
        }

        .hero-right-animate {
            opacity: 0;
            animation: fadeInUpSoft 0.7s ease-out 0.4s forwards;
        }

        /* SCROLL REVEAL ANIMATION */
        .reveal-on-scroll {
            opacity: 0;
            transform: translateY(18px);
            transition: opacity 0.7s ease-out, transform 0.7s ease-out;
        }

        .reveal-on-scroll.reveal-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .reveal-delay-1 {
            transition-delay: 0.1s;
        }

        .reveal-delay-2 {
            transition-delay: 0.2s;
        }

        /* FLOATING ELEMENTS */
        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(40px);
            opacity: 0.3;
            z-index: 0;
        }
        .blob-1 {
            width: 220px;
            height: 220px;
            background: #0ea5e9;
            top: 15%;
            right: -60px;
            animation: float1 18s infinite alternate ease-in-out;
        }

        .blob-2 {
            width: 260px;
            height: 260px;
            background: #facc15;
            bottom: -80px;
            left: -60px;
            animation: float2 22s infinite alternate ease-in-out;
        }

        @keyframes float1 {
            from { transform: translate3d(0,0,0); }
            to { transform: translate3d(-40px,20px,0); }
        }
        @keyframes float2 {
            from { transform: translate3d(0,0,0); }
            to { transform: translate3d(30px,-30px,0); }
        }

        /* GLOW YANG MENGIKUTI MOUSE (DESKTOP) */
        .cursor-glow {
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
            height: 220px;
            border-radius: 999px;
            pointer-events: none;
            z-index: 0;
            background:
                radial-gradient(circle at center,
                    rgba(56, 189, 248, 0.55),
                    rgba(59, 130, 246, 0.15),
                    transparent 65%);
            mix-blend-mode: screen;
            filter: blur(6px);
            opacity: 0;
            transform: translate3d(-9999px, -9999px, 0);
            transition: opacity 0.35s ease-out;
        }

        /* SECTION DOWNLOAD BUTTON */
        .download-section {
            padding: 0.2rem 0 0.5rem;
        }

        .btn-download-gradient {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.85rem 1.6rem;
            border-radius: 999px;
            border: none;
            font-size: 0.9rem;
            font-weight: 600;
            color: #0f172a;
            text-decoration: none;
            cursor: pointer;

            background-image: linear-gradient(120deg,
                #facc15,
                #fb923c,
                #0ea5e9,
                #22c55e,
                #facc15);
            background-size: 230% 230%;
            animation: gradientFlow 8s ease-in-out infinite;
            box-shadow: 0 10px 25px rgba(15,23,42,0.5);
        }

        .btn-download-gradient i {
            font-size: 1.05rem;
        }

        .btn-download-gradient:hover {
            box-shadow: 0 14px 32px rgba(15,23,42,0.7);
        }

        @keyframes gradientFlow {
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

        @media (max-width: 768px) {
            body {
                overflow-x: hidden;
            }
            .hero-section {
                padding-top: 1.2rem;
            }
            .nav-buttons {
                margin-top: .75rem;
            }
            .feature-card {
                margin-top: 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .navbar-custom .container,
            .hero-section .container,
            .download-section .container,
            footer .container {
                padding-left: 1.25rem;
                padding-right: 1.25rem;
            }
        }
    </style>
</head>
<body>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>
<div class="cursor-glow"></div>

<div class="landing-wrapper">
    <!-- NAVBAR -->
    <nav class="navbar navbar-custom">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center justify-content-between w-100">
                <a class="navbar-brand" href="#">
                    <div class="navbar-brand-logo">
                        <img src="{{ asset('images/logo_qr.png') }}" alt="Logo BBWS Brantas">
                    </div>
                    <div>
                        <div>Sistem Manajemen Rapat</div>
                        <span class="nav-badge">BBWS Brantas</span>
                    </div>
                </a>

                <div class="nav-buttons d-flex flex-wrap gap-2 mt-2 mt-md-0">
                    <!-- Admin Panel -->
                    <a href="{{ route('login') }}" class="btn btn-admin btn-sm">
                        <i class="bi bi-shield-lock me-1"></i> Admin Panel
                    </a>
                    <!-- PIC Panel (sementara arahkan ke login sama) -->
                    <a href="{{ route('login') }}" class="btn btn-pic btn-sm">
                        <i class="bi bi-people me-1"></i> PIC Panel
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <main class="hero-section">
        <div class="container">
            <div class="row align-items-center g-4">
                <!-- KIRI: TEKS -->
                <div class="col-lg-7 hero-left-animate">
                    <h1 class="hero-title">
                        Portal Rapat <span>Terintegrasi</span><br>
                        untuk Internal & Tamu
                    </h1>
                    <p class="hero-subtitle">
                        Kelola undangan, absensi QR, file rapat, dan monitoring kehadiran dalam satu sistem.
                        Halaman ini adalah landing page umum. Akses admin dan PIC hanya melalui tombol khusus.
                    </p>

                    <div class="hero-pills">
                        <div class="hero-pill">
                            <i class="bi bi-qr-code-scan me-1"></i> Absensi QR Tamu
                        </div>
                        <div class="hero-pill">
                            <i class="bi bi-people-fill me-1"></i> Internal & Guest Mode
                        </div>
                        <div class="hero-pill">
                            <i class="bi bi-cloud-arrow-down me-1"></i> Distribusi File Rapat
                        </div>
                    </div>

                    <div class="hero-actions">
                        <a href="{{ route('login') }}" class="btn btn-admin btn-lg d-flex align-items-center gap-2">
                            <i class="bi bi-speedometer2"></i>
                            <span>Masuk Admin Panel</span>
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg d-flex align-items-center gap-2" style="border-radius: .9rem;">
                            <i class="bi bi-person-badge"></i>
                            <span>Masuk PIC</span>
                        </a>
                    </div>

                    <div class="hero-demo">
                        <i class="bi bi-info-circle-fill"></i>
                        <span>Untuk tamu, akses hanya melalui QR / link khusus yang disediakan panitia.</span>
                    </div>

                    <!-- SECTION CARA TAMU MASUK -->
                    <div class="section-steps mt-4 reveal-on-scroll reveal-delay-1">
                        <div class="steps-title">Cara tamu mengisi kehadiran</div>
                        <div class="steps-caption">
                            Tamu tidak login dari sini. Mereka menggunakan QR code/link yang diberikan admin atau PIC.
                        </div>
                        <div class="step-list">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-text">
                                    Scan <span class="step-highlight">QR code rapat</span> yang ditempel/ditampilkan oleh panitia.
                                </div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-text">
                                    Isi <span class="step-highlight">form kehadiran tamu</span> pada halaman khusus rapat tersebut.
                                </div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-text">
                                    Setelah berhasil, tamu otomatis masuk ke <span class="step-highlight">dashboard tamu</span> untuk melihat info dan file rapat.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KANAN: KARTU DEMO -->
                <div class="col-lg-5 hero-right-animate">
                    <div class="feature-card reveal-on-scroll reveal-delay-2">
                        <div class="feature-badge-corner"></div>
                        <div class="feature-chip">Demo Tampilan</div>
                        <div class="feature-main">
                            Panel Ringkas untuk Rapat & Absensi
                        </div>
                        <div class="feature-desc">
                            Tampilan ini hanya contoh ringkas untuk mendemonstrasikan konsep dashboard rapat yang akan diakses oleh admin dan PIC.
                        </div>

                        <ul class="feature-list mb-2">
                            <li><i class="bi bi-check-circle-fill"></i> Monitoring absensi internal & tamu.</li>
                            <li><i class="bi bi-check-circle-fill"></i> Akses file rapat terpusat.</li>
                            <li><i class="bi bi-check-circle-fill"></i> QR code unik per rapat.</li>
                        </ul>

                        <div class="feature-mini">
                            <div class="mini-card">
                                <div class="mini-label">Mode Admin</div>
                                <div class="mini-value">
                                    Kelola rapat, user, & absensi.
                                </div>
                            </div>
                            <div class="mini-card">
                                <div class="mini-label">Mode PIC</div>
                                <div class="mini-value">
                                    Pantau kehadiran & verifikasi tamu.
                                </div>
                            </div>
                            <div class="mini-card">
                                <div class="mini-label">Mode Tamu</div>
                                <div class="mini-value">
                                    Masuk via QR & lihat info rapat.
                                </div>
                            </div>
                            <div class="mini-card">
                                <div class="mini-label">Demo Sistem</div>
                                <div class="mini-value">
                                    Cocok untuk presentasi & uji coba.
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 d-flex align-items-center gap-2" style="font-size: 0.78rem; color:#9ca3af;">
                            <i class="bi bi-shield-check text-success"></i>
                            <span>Akses admin & PIC dilindungi melalui halaman login terpisah.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- DOWNLOAD BUTTON SECTION -->
    <section class="download-section reveal-on-scroll reveal-delay-1">
        <div class="container text-center">
            {{-- Ganti href="#" dengan route() milikmu nanti --}}
            <a href="#"
               class="btn-download-gradient">
                <i class="bi bi-download"></i>
                <span>Download Aplikasi</span>
            </a>
        </div>
    </section>

    <footer class="py-3 mt-auto reveal-on-scroll reveal-delay-1">
        <div class="container text-center" style="font-size: 0.78rem; color:#9ca3af;">
            &copy; {{ date('Y') }} BBWS Brantas &mdash; Sistem Manajemen Rapat · Magang UNTAG Surabaya 2025
        </div>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Scroll reveal
        const revealElements = document.querySelectorAll('.reveal-on-scroll');

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('reveal-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.15
            });

            revealElements.forEach(el => observer.observe(el));
        } else {
            revealElements.forEach(el => el.classList.add('reveal-visible'));
        }

        // Parallax hover untuk feature-card
        const featureCard = document.querySelector('.feature-card');
        if (featureCard) {
            featureCard.addEventListener('mousemove', (e) => {
                const rect = featureCard.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;

                const rotateX = ((y - centerY) / centerY) * -4;
                const rotateY = ((x - centerX) / centerX) * 4;

                featureCard.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(0)`;
                featureCard.classList.add('parallax-active');
            });

            featureCard.addEventListener('mouseleave', () => {
                featureCard.style.transform = 'rotateX(0deg) rotateY(0deg) translateZ(0)';
                featureCard.classList.remove('parallax-active');
            });
        }

        // Cursor glow (desktop only)
        const glow = document.querySelector('.cursor-glow');
        let rafId = null;

        function handleMouseMove(e) {
            if (!glow) return;

            // hanya aktif di layar lebar (desktop-ish)
            if (window.innerWidth < 992) {
                glow.style.opacity = '0';
                return;
            }

            const glowSize = 220;
            const x = e.clientX - glowSize / 2;
            const y = e.clientY - glowSize / 2;

            glow.style.opacity = '1';

            if (rafId) cancelAnimationFrame(rafId);
            rafId = requestAnimationFrame(() => {
                glow.style.transform = `translate3d(${x}px, ${y}px, 0)`;
            });
        }

        function handleMouseLeave() {
            if (!glow) return;
            glow.style.opacity = '0';
        }

        window.addEventListener('mousemove', handleMouseMove);
        window.addEventListener('mouseleave', handleMouseLeave);
    });
</script>
</body>
</html>
