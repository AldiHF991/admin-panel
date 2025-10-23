<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // Hapus data user yang ada untuk menghindari duplikat saat seeding ulang
        User::truncate();

        // 3. Aktifkan kembali pengecekan foreign key
        Schema::enableForeignKeyConstraints();

        // Buat Akun Admin Utama
        User::create([
            'name' => 'Admin BBWS',
            'username' => 'admin',
            'email' => 'admin@bbws.com',
            'password' => '1', // Hashing akan ditangani otomatis oleh model User
            'id_role' => 1, // ID 1 untuk Admin
            'id_division' => 2000, // Sesuaikan jika perlu
            'phone' => '081234567890',
            'gender' => 'Male',
        ]);

        // (Opsional) Buat Akun PIC
        User::create([
            'name' => 'PIC User',
            'username' => 'pic',
            'email' => 'pic@bbws.com',
            'password' => '1', // Hashing akan ditangani otomatis oleh model User
            'id_role' => 2, // ID 2 untuk PIC
            'id_division' => 2001, // Sesuaikan jika perlu
            'phone' => '081234567891',
            'gender' => 'Female',
        ]);

        User::create([
            'name' => 'Users',
            'username' => 'user',
            'email' => 'user@bbws.com',
            'password' => '1', // Hashing akan ditangani otomatis oleh model User
            'id_role' => 3, // ID 2 untuk PIC
            'id_division' => 2002, // Sesuaikan jika perlu
            'phone' => '081234567891',
            'gender' => 'Female',
        ]);
    }
}
