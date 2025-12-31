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
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->time('work_start_time')->default('09:00:00');
            $table->time('late_after')->default('09:15:00');
            $table->time('work_end_time')->default('17:00:00');

            $table->integer('half_day_minutes')->default(240);
            $table->integer('full_day_minutes')->default(480);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};
