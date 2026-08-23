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
        Schema::table('payment_verifications', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_verifications', 'waktu_kehadiran')) {
                $table->string('waktu_kehadiran')->nullable()->after('validated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_verifications', function (Blueprint $table) {
            if (Schema::hasColumn('payment_verifications', 'waktu_kehadiran')) {
                $table->dropColumn('waktu_kehadiran');
            }
        });
    }
};
