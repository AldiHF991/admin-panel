<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Room extends Model
{
    use HasFactory;

    protected $table = 'room';

    protected $primaryKey = 'id_room';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'room',
        'id_cabang',
        'status_ruangan_id',
    ];

    /**
     * Mendefinisikan relasi "belongsTo" ke model Cabang.
     */
    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang', 'id');
    }

    /**
     * Mendefinisikan relasi "hasMany" ke model Rapat.
     */
    public function rapat()
    {
        return $this->hasMany(Rapat::class, 'id_room', 'id_room');
    }

    public function statusRuangan(): BelongsTo
    {
        return $this->belongsTo(StatusRuangan::class, 'status_ruangan_id');
    }
}
