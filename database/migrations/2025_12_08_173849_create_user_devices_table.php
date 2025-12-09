<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();

            // user
            $table->uuid('user_id');

            // device identity
            $table->string('device_fingerprint', 128)->unique();   // hashed fingerprint
            $table->string('hardware_id', 128)->nullable();        // androidId / identifierForVendor
            $table->string('app_instance_id', 128)->nullable();    // UUID from secure storage

            // additional metadata
            $table->string('manufacturer', 128)->nullable();
            $table->string('model', 128)->nullable();
            $table->string('os_version', 128)->nullable();
            $table->string('build_id', 128)->nullable();           // optional: Android build ID / display

            // status
            $table->boolean('is_active')->default(true);           // true = device yg sedang terkunci untuk user
            $table->timestamp('locked_at')->nullable();            // kapan device dipatenkan

            $table->timestamps();

            // relations
            $table->foreign('user_id')
                ->references('id_user')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
