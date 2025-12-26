<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('basic_salary', 15, 2);
            $table->decimal('allowances', 15, 2)->default(0);
            $table->decimal('deductions', 15, 2)->default(0);
            $table->decimal('savings_deduction', 15, 2)->default(0); // For wallet
            $table->decimal('net_salary', 15, 2);
            $table->year('year');
            $table->integer('month'); // 1-12
            $table->enum('status', ['draft', 'processed', 'paid'])->default('draft');
            $table->string('payslip_path')->nullable(); // PDF file path
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
