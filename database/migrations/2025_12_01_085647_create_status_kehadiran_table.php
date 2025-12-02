<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('status_kehadiran', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('nama_status', 50);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_kehadiran');
    }
};
