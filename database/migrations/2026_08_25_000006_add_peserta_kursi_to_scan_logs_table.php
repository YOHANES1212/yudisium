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
        Schema::table('scan_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('scan_logs', 'peserta_kursi')) {
                $table->string('peserta_kursi', 20)->nullable()->after('peserta_prodi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scan_logs', function (Blueprint $table) {
            if (Schema::hasColumn('scan_logs', 'peserta_kursi')) {
                $table->dropColumn('peserta_kursi');
            }
        });
    }
};
