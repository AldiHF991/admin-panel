<?php

namespace App\Exports;

use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AbsensiRapatExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    protected $absensiCollection;

    public function __construct($absensiCollection)
    {
        $this->absensiCollection = $absensiCollection;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Mengembalikan koleksi absensi yang sudah difilter dari controller
        return $this->absensiCollection;
    }

    /**
     * Fungsi ini untuk mendefinisikan header pada file Excel.
     */
    public function headings(): array
    {
        return [
            'ID',
            'ID Rapat',
            'ID User',
            'Attendable ID',
            'Attendable Type',
            'ID Status Kehadiran',
            'Waktu Absen',
            'Device ID Log',
            'Device Token',
            'User Agent',
            'IP Address',
        ];
    }

    /**
     * Fungsi ini untuk memetakan data dari collection ke setiap baris Excel.
     *
     * @param  Absensi  $absensi
     */
    public function map($absensi): array
    {
        return [
            $absensi->id,
            $absensi->id_rapat,
            $absensi->id_user, // Nullable in DB
            $absensi->attendable_id,
            $absensi->attendable_type,
            $absensi->id_status_kehadiran,
            $absensi->waktu_absen,
            $absensi->device_id_log,
            $absensi->device_token,
            $absensi->user_agent,
            $absensi->ip_address,
        ];
    }
}
