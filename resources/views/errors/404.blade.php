<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Tidak Ditemukan - BBWS Brantas</title>
    <link rel="icon" href="{{ asset('images/logo_qr.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Warna Dominan Logo Kementerian PU */
            --pu-blue-dark: #001A33;    /* Biru sangat gelap untuk dasar background */
            --pu-blue-main: #003366;    /* Biru utama dari logo PU */
            --pu-blue-light: #00509E;   /* Biru lebih terang untuk aksen dan gradien */
            --pu-yellow: #F4C430;       /* Kuning emas dari logo PU */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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

            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Poppins", sans-serif;
            position: relative;
            overflow-y: auto; /* Allow scrolling */
            padding: 2rem;
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
            pointer-events: none;
        }

        @keyframes auroraAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .error-container {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 26, 51, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.1);
            padding: 2.5rem 2.5rem;
            max-width: 600px;
            width: 100%;
            text-align: center;
            transition: all 0.3s ease;
            z-index: 10;
            border-top: 5px solid var(--pu-yellow);
            border-bottom: 2px solid var(--pu-blue-main);
            position: relative;
            overflow: hidden;
        }

        .error-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(244, 196, 48, 0.1) 0%, transparent 70%);
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.1); }
        }

        .error-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0, 26, 51, 0.6);
        }

        .error-logo {
            max-width: 100px;
            height: auto;
            display: block;
            margin: 0 auto 1.5rem auto;
            position: relative;
            z-index: 1;
            animation: float 3s ease-in-out infinite;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.15));
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .error-code {
            font-size: 6rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--pu-blue-main) 0%, var(--pu-blue-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
            text-shadow: none;
            letter-spacing: -3px;
        }

        .error-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--pu-blue-main);
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
            letter-spacing: -0.3px;
        }

        .error-message {
            font-size: 1rem;
            color: #333;
            margin-bottom: 1.5rem;
            line-height: 1.6;
            position: relative;
            z-index: 1;
            font-weight: 400;
        }

        .error-icon {
            font-size: 3.5rem;
            color: var(--pu-yellow);
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
            animation: search 2s ease-in-out infinite;
            filter: drop-shadow(0 4px 8px rgba(244, 196, 48, 0.3));
        }

        @keyframes search {
            0%, 100% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.1) rotate(5deg); }
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--pu-blue-main) 0%, var(--pu-blue-light) 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 51, 102, 0.3);
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 51, 102, 0.4);
            color: white;
        }

        .btn-secondary-custom {
            background: white;
            border: 2px solid var(--pu-blue-main);
            color: var(--pu-blue-main);
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 51, 102, 0.15);
        }

        .btn-secondary-custom:hover {
            background: var(--pu-blue-main);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 51, 102, 0.3);
        }

        .error-details {
            background: linear-gradient(135deg, rgba(244, 196, 48, 0.15) 0%, rgba(244, 196, 48, 0.05) 100%);
            border-left: 4px solid var(--pu-yellow);
            border-right: 2px solid rgba(244, 196, 48, 0.3);
            padding: 1.25rem;
            border-radius: 10px;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            text-align: left;
            position: relative;
            z-index: 1;
            box-shadow: 0 2px 8px rgba(244, 196, 48, 0.12);
        }

        .error-details p {
            margin: 0;
            color: #2c3e50;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .error-details strong {
            color: var(--pu-blue-main);
            font-size: 1rem;
        }

        /* Tablet */
        @media (max-width: 992px) {
            .error-container {
                padding: 2rem 2rem;
                max-width: 550px;
            }

            .error-code {
                font-size: 5rem;
            }

            .error-title {
                font-size: 1.5rem;
            }
        }

        /* Mobile */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .error-container {
                padding: 2rem 1.5rem;
                border-radius: 16px;
            }

            .error-logo {
                max-width: 80px;
                margin-bottom: 1rem;
            }

            .error-code {
                font-size: 4rem;
                letter-spacing: -2px;
                margin-bottom: 0.75rem;
            }

            .error-title {
                font-size: 1.35rem;
                margin-bottom: 0.75rem;
            }

            .error-message {
                font-size: 0.95rem;
                margin-bottom: 1.25rem;
            }

            .error-icon {
                font-size: 2.5rem;
                margin-bottom: 0.75rem;
            }

            .btn-group {
                flex-direction: column;
                gap: 0.75rem;
            }

            .btn-primary-custom,
            .btn-secondary-custom {
                width: 100%;
                justify-content: center;
                padding: 0.75rem 1.5rem;
                font-size: 0.95rem;
            }

            .error-details {
                padding: 1rem;
                margin-top: 1.25rem;
            }

            .error-details p {
                font-size: 0.9rem;
            }

            .error-details strong {
                font-size: 0.95rem;
            }
        }

        /* Small Mobile */
        @media (max-width: 480px) {
            body {
                padding: 0.75rem;
            }

            .error-container {
                padding: 1.5rem 1.25rem;
            }

            .error-logo {
                max-width: 70px;
            }

            .error-code {
                font-size: 3.5rem;
            }

            .error-title {
                font-size: 1.2rem;
            }

            .error-message {
                font-size: 0.9rem;
            }

            .error-icon {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <img src="{{ asset('images/logo_qr.png') }}" alt="Logo BBWS Brantas" class="error-logo">
        
        <div class="error-icon">
            <i class="bi bi-search"></i>
        </div>

        <div class="error-code">404</div>
        
        <h1 class="error-title">Halaman Tidak Ditemukan</h1>
        
        <p class="error-message">
            Maaf, halaman yang Anda cari tidak dapat ditemukan. 
            Halaman mungkin telah dipindahkan, dihapus, atau URL yang Anda masukkan salah.
        </p>

        <div class="error-details">
            <p><strong><i class="bi bi-info-circle me-2"></i>Informasi:</strong></p>
            <p class="mt-2">
                Pastikan URL yang Anda masukkan sudah benar. Jika Anda yakin URL benar, 
                halaman mungkin telah dihapus atau dipindahkan. Silakan kembali ke halaman utama atau hubungi administrator.
            </p>
        </div>

        <div class="btn-group mt-4">
            @auth
                @if(auth()->user()->id_role == 2)
                    <a href="{{ route('pic.dashboard') }}" class="btn-primary-custom">
                        <i class="bi bi-speedometer2"></i>
                        Kembali ke Dashboard
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="btn-primary-custom">
                        <i class="bi bi-speedometer2"></i>
                        Kembali ke Dashboard
                    </a>
                @endif
            @else
                <a href="{{ route('landingPage') }}" class="btn-primary-custom">
                    <i class="bi bi-house"></i>
                    Kembali ke Halaman Utama
                </a>
            @endauth
            <a href="javascript:history.back()" class="btn-secondary-custom">
                <i class="bi bi-arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>

    <script>
        // Script untuk mengikuti cursor
        document.addEventListener('mousemove', (e) => {
            document.documentElement.style.setProperty('--x', e.clientX + 'px');
            document.documentElement.style.setProperty('--y', e.clientY + 'px');
        });
    </script>
</body>
</html>

