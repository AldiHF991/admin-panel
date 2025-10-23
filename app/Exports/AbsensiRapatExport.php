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
            'Nama Peserta',
            'Email',
            'Waktu Absen',
            'Divisi',
        ];
    }

    /**
     * Fungsi ini untuk memetakan data dari collection ke setiap baris Excel.
     *
     * @param  Absensi  $absensi
     */
    public function map($absensi): array
    {
        // Menggunakan null-safe operator (?->) untuk keamanan jika relasi user atau division tidak ada
        return [
            $absensi->user?->name ?? 'N/A',
            $absensi->user?->email ?? 'N/A',
            $absensi->waktu_absen,
            $absensi->user?->division?->division_name ?? 'N/A',
        ];
    }
}
