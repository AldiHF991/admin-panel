@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Daftar Absensi Rapat</h4>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>ID Rapat:</strong> <span id="id-rapat">{{ $id }}</span></p>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Peserta yang Sudah Melakukan Absensi</h5>
            <small id="last-update" class="text-light fst-italic"></small>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="table-success">
                    <tr>
                        <th>#</th>
                        <th>Nama Peserta</th>
                        <th>Email</th>
                        <th>Waktu Absensi</th>
                        <th>Status Kehadiran</th>
                    </tr>
                </thead>
                <tbody id="tabel-peserta">
                    <tr><td colspan="5" class="text-center text-muted">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ url('/meetings') }}" class="btn btn-secondary">← Kembali ke Daftar Rapat</a>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const rapatId = "{{ $id }}";
    const apiUrl = `http://192.168.51.170:8000/api/rapat/${rapatId}/absensi`; // Gunakan rapatId dari PHP
    const tbody = document.getElementById("tabel-peserta");
    const lastUpdate = document.getElementById("last-update");

    async function loadAbsensi() {
        try {
            const res = await fetch(apiUrl);
            
            // Cek status response
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            
            const json = await res.json();
            console.log('Data dari API:', json); // Untuk debugging

            const data = json.data;
            tbody.innerHTML = "";

            if (!data || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Belum ada yang absen.</td></tr>`;
                return;
            }

            data.forEach((item, i) => {
                const user = item.user || {};
                const waktu = new Date(item.waktu_absen).toLocaleString('id-ID', {
                    dateStyle: 'short',
                    timeStyle: 'medium'
                });
                
                // status kehadiran bisa dikustom, misal id_status_kehadiran 2 = hadir
                const status = item.id_status_kehadiran == 2 ? 'Hadir' : 'Tidak Hadir';
                const badge = item.id_status_kehadiran == 2 ? 'success' : 'danger';

                const row = `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${user.name ?? '-'}</td>
                        <td>${user.email ?? '-'}</td>
                        <td>${waktu}</td>
                        <td><span class="badge bg-${badge}">${status}</span></td>
                    </tr>`;
                tbody.insertAdjacentHTML("beforeend", row);
            });

            // update waktu refresh terakhir
            const now = new Date().toLocaleTimeString('id-ID');
            lastUpdate.textContent = `Terakhir diperbarui: ${now}`;
        } catch (err) {
            console.error("Gagal memuat absensi:", err);
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Gagal memuat data absensi: ${err.message}</td></tr>`;
        }
    }

    // 🔁 Jalankan pertama kali, lalu update tiap 5 detik
    loadAbsensi();
    setInterval(loadAbsensi, 5000);
});
</script>
@endsection