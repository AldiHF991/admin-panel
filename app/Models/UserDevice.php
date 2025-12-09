<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_fingerprint',
        'hardware_id',
        'app_instance_id',
        'manufacturer',
        'model',
        'os_version',
        'build_id',
        'is_active',
        'locked_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'locked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
