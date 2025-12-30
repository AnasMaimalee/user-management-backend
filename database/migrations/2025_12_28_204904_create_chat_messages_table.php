<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('chat_room_id');
            $table->uuid('user_id')->nullable();     // Admin or user
            $table->uuid('employee_id')->nullable(); // Employee sender

            $table->text('message');

            $table->timestamps();

            $table->foreign('chat_room_id')->references('id')->on('chat_rooms')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};

