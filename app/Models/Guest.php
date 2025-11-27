<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Guest extends Model
{
    use HasUuids;

    protected $primaryKey = 'id_guest';
   
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    
    protected $fillable = [
        'nama',
        'jabatan',
        'asal_instansi',
        'nomor',
    ];
    /**
         * Mendefinisikan relasi polimorfik ke model Absensi.
         */
        public function absensi()
        {
            // PERBAIKAN: Tentukan local key secara eksplisit karena primary key model ini adalah 'id_guest'.
            return $this->morphMany(Absensi::class, 'attendable', 'attendable_type', 'attendable_id', 'id_guest');
        }
}
