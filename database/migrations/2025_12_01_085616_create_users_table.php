<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama', 100);
            $table->string('nip', 50)->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->unsignedBigInteger('id_cabang')->nullable();
            $table->string('jabatan')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
