<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Hadir Rapat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold mb-2 text-center">Daftar Hadir Rapat</h1>

        <div class="text-center mb-6 bg-blue-50 p-4 rounded-lg border border-blue-100 shadow-sm max-w-md mx-auto">
            <div class="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1">Waktu Saat Ini (WIB)</div>
            <div id="clock-display" class="text-3xl font-mono font-bold text-gray-800">--:--:--</div>
            <div id="date-display" class="text-sm text-gray-500 mt-1">...</div>
        </div>
        <div class="text-center text-gray-600 mb-8">
            <p>ID Rapat: <span class="font-semibold text-gray-800">{{ $rapatId }}</span></p>
            <p class="mt-1">Jumlah Peserta Hadir: <span id="attendance-count" class="font-bold text-blue-600">0</span></p>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            No
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Nama
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Email
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Waktu Absen
                        </th>
                    </tr>
                </thead>
                <tbody id="attendance-list">
                    </tbody>
            </table>
        </div>
    </div>

    <script type="module">
        // --- BAGIAN JAM REALTIME ---
        function updateRealtimeClock() {
            const now = new Date();
            
            // Format Waktu (WIB)
            const timeOptions = { 
                timeZone: 'Asia/Jakarta', 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit', 
                hour12: false 
            };
            
            // Format Tanggal (Indonesia)
            const dateOptions = { 
                timeZone: 'Asia/Jakarta', 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };

            const timeString = new Intl.DateTimeFormat('id-ID', timeOptions).format(now);
            const dateString = new Intl.DateTimeFormat('id-ID', dateOptions).format(now);

            const clockEl = document.getElementById('clock-display');
            const dateEl = document.getElementById('date-display');

            if(clockEl) clockEl.textContent = timeString.replace(/\./g, ':');
            if(dateEl) dateEl.textContent = dateString;
        }

        // Jalankan jam setiap detik
        setInterval(updateRealtimeClock, 1000);
        updateRealtimeClock(); // Jalankan langsung saat load
        // ---------------------------


        // --- LOGIKA ABSENSI ---
        
        // Ambil data awal yang dikirim dari server
        const initialData = @json($initialAbsensi);

        // Fungsi untuk memformat waktu (Untuk tabel)
        function formatTime(dateTimeString) {
            const date = new Date(dateTimeString);
            // Gunakan timeZone Asia/Jakarta juga agar konsisten dengan jam di atas
            return date.toLocaleTimeString('id-ID', { 
                timeZone: 'Asia/Jakarta',
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            }).replace(/\./g, ':');
        }

        // Fungsi untuk merender daftar absensi
        function renderAttendance(data) {
            const attendanceList = document.getElementById('attendance-list');
            attendanceList.innerHTML = ''; // Kosongkan daftar
            document.getElementById('attendance-count').textContent = data.length;

            if (data.length === 0) {
                attendanceList.innerHTML = `<tr><td colspan="4" class="text-center p-5 text-gray-500">Belum ada peserta yang hadir.</td></tr>`;
                return;
            }

            // Urutkan data berdasarkan waktu absen terbaru di atas
            data.sort((a, b) => new Date(b.waktu_absen) - new Date(a.waktu_absen));

            data.forEach((item, index) => {
                const row = `
                    <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-5 py-4 text-sm text-gray-700">${index + 1}</td>
                        <td class="px-5 py-4 text-sm font-medium text-gray-900">${item.user.name}</td>
                        <td class="px-5 py-4 text-sm text-gray-600">${item.user.email}</td>
                        <td class="px-5 py-4 text-sm text-gray-600 font-mono">${formatTime(item.waktu_absen)}</td>
                    </tr>
                `;
                attendanceList.innerHTML += row;
            });
        }

        // Panggil renderAttendance sekali saat halaman dimuat dengan data awal
        document.addEventListener('DOMContentLoaded', () => {
            // console.log('Memuat data awal:', initialData); // Debug
            renderAttendance(initialData);
        });

        // Mendengarkan channel broadcast
        Echo.channel('Absensi.Rapat.{{ $rapatId }}')
            .listen('AbsensiUpdated', (e) => {
                console.log('Event diterima:', e);
                renderAttendance(e.absensi);

                // Beri sorotan pada baris pertama (data terbaru)
                const firstRow = document.querySelector('#attendance-list tr:first-child');
                if (firstRow) {
                    firstRow.classList.remove('bg-white'); // Hapus background putih default
                    firstRow.classList.add('bg-green-100');
                    setTimeout(() => {
                        firstRow.classList.remove('bg-green-100');
                        firstRow.classList.add('bg-white'); // Kembalikan
                    }, 2000);
                }
            });
    </script>
</body>
</html>