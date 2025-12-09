<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasUuids;

    protected $primaryKey = 'id';

    protected $table = 'absensi';

    public $timestamps = false;

    protected $fillable = [
        'id_rapat',
        'attendable_id',
        'attendable_type',
        'waktu_absen',
        'id_status_kehadiran',
        'face_photo',
        'device_id_log',
        'device_token',
        'user_agent',
        'ip_address',
    ];

    /**
     * Mendefinisikan relasi polimorfik "attendable".
     * Ini memungkinkan absensi dimiliki oleh User atau Guest.
     */
    public function attendable()
    {
        // PERBAIKAN: Definisikan morphTo dengan nama, tipe, dan ID secara eksplisit
        return $this->morphTo('attendable', 'attendable_type', 'attendable_id');
    }

    public function rapat()
    {
        return $this->belongsTo(Rapat::class, 'id_rapat');
    }
}
