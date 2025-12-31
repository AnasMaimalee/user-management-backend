<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('biometric_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->ipAddress('ip');
            $table->integer('port')->default(4370);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biometric_devices');
    }
};
