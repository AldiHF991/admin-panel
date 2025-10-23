<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasUuids;

    protected $primaryKey = 'id_absensi';
    protected $table = 'absensi';

    public $timestamps = false;

    protected $fillable = [
        'id_rapat', 'id_user', 'waktu_absen', 'id_status_kehadiran',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function rapat()
    {
        return $this->belongsTo(Rapat::class, 'id_rapat');
    }
}
