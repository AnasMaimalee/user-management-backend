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
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('employee_id');
            $table->string('device_user_id');
            $table->uuid('biometric_device_id')->nullable();

            $table->timestamp('punched_at');
            $table->string('status')->nullable(); // check-in / check-out (optional)

            $table->timestamps();

            $table->index(['employee_id', 'punched_at']);

            $table->foreign('employee_id')
                ->references('id')->on('employees')
                ->cascadeOnDelete();

            $table->foreign('biometric_device_id')
                ->references('id')->on('biometric_devices')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }

};
