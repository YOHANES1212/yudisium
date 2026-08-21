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
            $table->string('nomor_kursi', 20)->nullable()->after('status_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_verifications', function (Blueprint $table) {
            $table->dropColumn('nomor_kursi');
        });
    }
};
