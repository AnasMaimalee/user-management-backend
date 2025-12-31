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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('device_user_id')->nullable()->unique()->after('id');
            $table->timestamp('fingerprint_enrolled_at')->nullable()->after('device_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'device_user_id',
                'fingerprint_enrolled_at',
            ]);
        });
    }
};
