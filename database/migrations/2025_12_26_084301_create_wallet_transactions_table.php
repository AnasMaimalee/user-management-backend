<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('wallet_id')->constrained()->cascadeOnDelete();

            $table->decimal('amount', 15, 2);
            $table->enum('type', ['deposit', 'withdrawal', 'adjustment', 'loan_repayment']);
            $table->string('reason')->nullable();       // why transaction happened
            $table->string('reference')->nullable();    // e.g., loan id or external reference
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('approved');
            $table->foreignUuid('processed_by')->nullable()->constrained('users');
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->index(['wallet_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
