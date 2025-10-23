<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RapatPeserta extends Pivot
{
    use HasUuids;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'rapat_peserta';

    /**
     * Indicates if the IDs are auto-incrementing.
     * Karena kita menggunakan UUID, ini harus false.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Tentukan bahwa tipe data Primary Key adalah string (UUID).
     * Ini otomatis diurus oleh HasUuids.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_rapat',
        'id_division',
        'id_user', // Tambahkan id_user agar bisa diisi juga
    ];
}
