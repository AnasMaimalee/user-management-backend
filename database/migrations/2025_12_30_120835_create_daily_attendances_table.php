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
        Schema::create('daily_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('employee_id');
            $table->date('attendance_date');

            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();

            $table->integer('worked_minutes')->default(0);

            $table->enum('status', [
                'present',
                'late',
                'absent',
                'on_leave',
                'holiday',
            ]);

            $table->timestamps();

            $table->unique(['employee_id', 'attendance_date']);

            $table->foreign('employee_id')
                ->references('id')->on('employees')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_attendances');
    }
};
