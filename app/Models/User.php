<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    protected $primaryKey = 'id_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_role',
        'id_division',
        'username',
        'name', // Sesuai dengan migrasi database
        'email',
        'phone',
        'gender',
        'password',
        'photo',
    ];

    /**
     * Accessor untuk kompatibilitas 'nama' -> 'name'
     */
    public function getNamaAttribute()
    {
        return $this->attributes['name'];
    }

    /**
     * Mutator untuk kompatibilitas 'nama' -> 'name'
     */
    public function setNamaAttribute($value)
    {
        $this->attributes['name'] = $value;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            // PERBAIKAN: Cast id_role dan id_division sebagai integer
            'id_role' => 'integer',
            'id_division' => 'integer',
        ];
    }

    /**
     * Mendefinisikan relasi "belongsTo" ke model Division.
     */
    public function division()
    {
        return $this->belongsTo(Division::class, 'id_division', 'id_division');
    }

    /**
     * Mendefinisikan relasi "belongsTo" ke model Role.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    /**
     * Mendefinisikan relasi "hasMany" ke model Rapat (sebagai pengaju).
     * CATATAN: Sesuaikan dengan model Rapat yang sebenarnya
     */
    public function rapatDiajukan()
    {
        return $this->hasMany(\App\Models\Rapat::class, 'id_user_pengaju', 'id');
    }

    // PERBAIKAN: Accessor untuk mendapatkan nama role dengan mudah
    public function getRoleNameAttribute()
    {
        return $this->role ? $this->role->role : null;
    }

    // PERBAIKAN: Accessor untuk mendapatkan nama division dengan mudah
    public function getDivisionNameAttribute()
    {
        return $this->division ? $this->division->division_name : null;
    }

    public function absensi()
    {
        // PERBAIKAN: Tentukan local key secara eksplisit karena primary key model ini bukan 'id'.
        // Laravel akan otomatis menangani 'attendable_type' dan 'attendable_id'.
        return $this->morphMany(Absensi::class, 'attendable', 'attendable_type', 'attendable_id', 'id_user');
    }
}
