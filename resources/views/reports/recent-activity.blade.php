<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Laporan Aktivitas Rapat' }}</title>
    <link rel="icon" href="{{ asset('images/logo_qr.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to top, #f2f4f6, #ffffff);
            min-height: 100vh;
        }
        .report-header {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white;
            padding: 3rem 1.5rem;
            border-radius: .75rem;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }

        /* Print-specific styles */
        @media print {
            body {
                background: #fff !important;
                font-size: 10pt;
            }

            .no-print {
                display: none !important;
            }

            .print-header {
                display: block !important;
                margin-bottom: 2rem;
                border-bottom: 2px solid #000;
                padding-bottom: 1rem;
                /* Menambahkan flex untuk alignment logo */
                display: flex !important;
                align-items: center;
                gap: 1.5rem;
            }

            .print-logo {
                height: 50px; /* Atur ukuran logo sesuai kebutuhan */
            }

            .container {
                max-width: 100% !important;
                padding: 0 !important;
            }

            .card {
                box-shadow: none !important;
                border: none !important;
            }

            .table, .table th, .table td {
                border: 1px solid #dee2e6 !important;
                color: #000 !important;
            }

            .badge {
                border: 1px solid #6c757d;
                background-color: #fff !important;
                color: #000 !important;
                font-weight: normal;
            }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="print-header" style="display: none; ">
            <img src="{{ asset('images/logo_qr.png') }}" alt="Logo Perusahaan" class="print-logo">
            <div>
                <h4 class="fw-bold mb-1">Laporan Aktivitas Rapat</h4>
                <p class="mb-0 text-muted">Data 3 Hari Terakhir</p>
                <p class="mb-0 text-muted" style="font-size: 0.8rem;">
                    Dicetak pada: {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y, HH:mm') }} WIB
                </p>
            </div>
        </div>

        <div class="report-header text-center mb-4 no-print">
            <h1 class="display-6 fw-bold">Laporan Aktivitas Rapat</h1>
            <p class="lead mb-0">Data 3 Hari Terakhir</p>
        </div>

        <div class="card border-0 shadow-sm mb-4 no-print">
            <div class="card-body text-center">
                <h5 id="realtime-clock-date" class="mb-1 text-muted"></h5>
                <h3 id="realtime-clock-time" class="fw-bold text-primary mb-0"></h3>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center no-print">
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
                </a>
                <div class="no-print">
                    <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Cetak Laporan
                    </button>
                </div>
            </div>
            <div class="card-body">
                @forelse ($rapatTigaHariTerakhir->groupBy('tanggal') as $tanggal => $rapats)
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            {{ \Carbon\Carbon::parse($tanggal)->isoFormat('dddd, D MMMM Y') }}
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 35%;">Judul Rapat</th>
                                        <th scope="col" style="width: 20%;">Waktu</th>
                                        <th scope="col" style="width: 15%;">Ruangan</th>
                                        <th scope="col" style="width: 15%;">Pengaju</th>
                                        <th scope="col" style="width: 15%;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rapats as $rapat)
                                        <tr>
                                            <td class="fw-medium">{{ $rapat->judul }}</td>
                                            <td>{{ \Carbon\Carbon::parse($rapat->waktu_start)->format('H:i') }} - {{ \Carbon\Carbon::parse($rapat->waktu_end)->format('H:i') }} WIB</td>
                                            <td>{{ $rapat->room->room ?? 'N/A' }}</td>
                                            <td>{{ $rapat->pengaju->name ?? 'N/A' }}</td>
                                            <td>
                                                @php
                                                    $statusText = $rapat->status->status_rapat ?? 'N/A';
                                                    $bgColor = 'secondary';
                                                    switch (strtolower($statusText)) {
                                                        case 'diterima': $bgColor = 'success'; break;
                                                        case 'ditolak': $bgColor = 'danger'; break;
                                                        case 'menunggu': case 'menunggu persetujuan': $bgColor = 'warning'; break;
                                                        case 'berlangsung': $bgColor = 'primary'; break;
                                                    }
                                                @endphp
                                                <span class="badge rounded-pill bg-{{ $bgColor }}">{{ $statusText }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <p class="mb-0">Tidak ada aktivitas rapat dalam 3 hari terakhir.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            const optionsDate = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                timeZone: 'Asia/Jakarta'
            };
            const optionsTime = {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
                timeZone: 'Asia/Jakarta'
            };

            document.getElementById('realtime-clock-date').textContent = now.toLocaleDateString('id-ID', optionsDate);
            document.getElementById('realtime-clock-time').textContent = now.toLocaleTimeString('id-ID', optionsTime) + ' WIB';
        }

        setInterval(updateClock, 1000);
        updateClock(); // Panggilan awal untuk menampilkan jam segera
    </script>
</body>
</html>