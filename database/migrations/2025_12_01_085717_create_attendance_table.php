<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('attendable_id'); // UUID user atau guest
            $table->string('attendable_type', 30); // App\Models\User atau Guest
            $table->uuid('id_rapat');
            $table->tinyInteger('id_status_kehadiran')->nullable();
            $table->timestamp('waktu_absen')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
