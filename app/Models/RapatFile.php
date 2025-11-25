<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RapatFile extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rapat_file';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_file';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_rapat',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
    ];

    /**
     * Get the rapat that owns the file.
     */
    public function rapat()
    {
        return $this->belongsTo(Rapat::class, 'id_rapat', 'id_rapat');
    }
}
