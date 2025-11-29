<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cabang extends Model
{
    use HasFactory;

    protected $table = 'cabang';

    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'cabang',
        'alamat',
    ];

    /**
     * Mendefinisikan relasi "hasMany" ke model Room.
     */
    public function room(): HasMany
    {
        // Parameter: (Model Terkait, Foreign Key di tabel room, Local Key di tabel cabang)
        return $this->hasMany(Room::class, 'id_cabang', 'id');
    }

    /**
     * Mendefinisikan relasi "hasMany" ke model Rapat.
     */
    public function rapat()
    {
        return $this->hasMany(Rapat::class, 'id_cabang', 'id');
    }
}
