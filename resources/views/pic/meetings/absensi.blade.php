<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Hadir Rapat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Auto refresh every 100 seconds --}}
    <meta http-equiv="refresh" content="100">

    {{-- Style untuk Watermark --}}
    <style>
        .watermark {
            position: fixed;
            bottom: 10px;
            right: 15px;
            opacity: 0.3;
            font-size: 12px;
            color: #000;
            text-align: right;
            pointer-events: none;
            z-index: 9999;
            line-height: 1.2;
        }
    </style>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-2">
            <h1 class="text-3xl font-bold text-center">Daftar Hadir Rapat</h1>
            {{-- Tombol Back ke Dashboard PIC --}}
            <a href="{{ route('pic.meetings.index') }}" 
               class="mt-2 sm:mt-0 inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-800 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>
        </div>

        <div class="text-center mb-6 bg-blue-50 p-4 rounded-lg border border-blue-100 shadow-sm max-w-md mx-auto">
            <div class="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1">Waktu Saat Ini (WIB)</div>
            <div id="clock-display" class="text-3xl font-mono font-bold text-gray-800">--:--:--</div>
            <div id="date-display" class="text-sm text-gray-500 mt-1">...</div>
        </div>
        <div class="text-center text-gray-600 mb-8">
            <p>Total Peserta Hadir: <span id="total-attendance-count" class="font-bold text-blue-600">0</span></p>
        </div>

        {{-- TABEL UNTUK KARYAWAN INTERNAL --}}
        <div class="mb-12">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Peserta Internal (<span id="internal-attendance-count">0</span>)</h2>
            <div class="bg-white shadow-md rounded-lg overflow-y-auto" style="max-height: 40vh;">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Divisi</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Waktu Absen</th>
                        </tr>
                    </thead>
                    <tbody id="internal-attendance-list">
                        {{-- Data internal diisi oleh JavaScript --}}
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TABEL UNTUK TAMU --}}
        <div>
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Peserta Tamu (<span id="guest-attendance-count">0</span>)</h2>
            <div class="bg-white shadow-md rounded-lg overflow-y-auto" style="max-height: 40vh;">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Jabatan</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Asal Instansi</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nomor WA</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Waktu Absen</th>
                        </tr>
                    </thead>
                    <tbody id="guest-attendance-list">
                        {{-- Data tamu diisi oleh JavaScript --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script type="module">
        // --- BAGIAN JAM REALTIME ---
        function updateRealtimeClock() {
            const now = new Date();
            const timeOptions = { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
            const dateOptions = { timeZone: 'Asia/Jakarta', weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const timeString = new Intl.DateTimeFormat('id-ID', timeOptions).format(now);
            const dateString = new Intl.DateTimeFormat('id-ID', dateOptions).format(now);
            const clockEl = document.getElementById('clock-display');
            const dateEl = document.getElementById('date-display');
            if(clockEl) clockEl.textContent = timeString.replace(/\./g, ':');
            if(dateEl) dateEl.textContent = dateString;
        }
        setInterval(updateRealtimeClock, 1000);
        updateRealtimeClock();

        // --- LOGIKA ABSENSI ---
        const initialData = @json($absensi);

        function formatTime(dateTimeString) {
            const date = new Date(dateTimeString);
            return date.toLocaleTimeString('id-ID', { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace(/\./g, ':');
        }

        // Fungsi untuk merender daftar absensi
        function renderAttendance(data) {
            const internalList = document.getElementById('internal-attendance-list');
            const guestList = document.getElementById('guest-attendance-list');
            internalList.innerHTML = '';
            guestList.innerHTML = '';

            // Pisahkan data menjadi internal dan guest
            const internalUsers = data.filter(item => item.attendable_type === 'App\\Models\\User' && item.attendable);
            const guestUsers = data.filter(item => item.attendable_type === 'App\\Models\\Guest' && item.attendable);

            // Update jumlah peserta
            document.getElementById('total-attendance-count').textContent = data.length;
            document.getElementById('internal-attendance-count').textContent = internalUsers.length;
            document.getElementById('guest-attendance-count').textContent = guestUsers.length;

            // Urutkan data berdasarkan waktu absen terbaru di atas
            internalUsers.sort((a, b) => new Date(b.waktu_absen) - new Date(a.waktu_absen));
            guestUsers.sort((a, b) => new Date(b.waktu_absen) - new Date(a.waktu_absen));

            // Render Peserta Internal
            if (internalUsers.length === 0) {
                internalList.innerHTML = `<tr><td colspan="4" class="text-center p-5 text-gray-500">Belum ada peserta internal yang hadir.</td></tr>`;
            } else {
                internalUsers.forEach((item, index) => {
                    const attendee = item.attendable;
                    const divisionName = attendee.division ? attendee.division.division_name : 'N/A';
                    const row = `
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-5 py-4 text-sm text-gray-700">${index + 1}</td>
                            <td class="px-5 py-4 text-sm font-medium text-gray-900">${attendee.nama || attendee.name}</td>
                            <td class="px-5 py-4 text-sm text-gray-600">${divisionName}</td>
                            <td class="px-5 py-4 text-sm text-gray-600 font-mono">${formatTime(item.waktu_absen)}</td>
                        </tr>`;
                    internalList.innerHTML += row;
                });
            }

            // Render Peserta Tamu
            if (guestUsers.length === 0) {
                guestList.innerHTML = `<tr><td colspan="6" class="text-center p-5 text-gray-500">Belum ada peserta tamu yang hadir.</td></tr>`;
            } else {
                guestUsers.forEach((item, index) => {
                    const attendee = item.attendable;
                    const row = `
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-5 py-4 text-sm text-gray-700">${index + 1}</td>
                            <td class="px-5 py-4 text-sm font-medium text-gray-900">${attendee.nama}</td>
                            <td class="px-5 py-4 text-sm text-gray-600">${attendee.jabatan || 'N/A'}</td>
                            <td class="px-5 py-4 text-sm text-gray-600">${attendee.asal_instansi}</td>
                            <td class="px-5 py-4 text-sm text-gray-600">${attendee.nomor || 'N/A'}</td>
                            <td class="px-5 py-4 text-sm text-gray-600 font-mono">${formatTime(item.waktu_absen)}</td>
                        </tr>`;
                    guestList.innerHTML += row;
                });
            }
        }

        // Panggil renderAttendance sekali saat halaman dimuat dengan data awal
        document.addEventListener('DOMContentLoaded', () => {
            renderAttendance(initialData);
        });
    </script>
</body>

{{-- Elemen untuk Watermark --}}
<div class="watermark">
    <div>Magang UNTAG Surabaya 2025</div>
    <div>Ilfath & Aldi</div>
</div>

</html>
