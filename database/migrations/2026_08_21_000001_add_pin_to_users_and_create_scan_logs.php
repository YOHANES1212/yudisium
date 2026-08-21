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
        Schema::table('users', function (Blueprint $table) {
            $table->string('pin', 6)->default('123456')->after('role');
        });

        Schema::create('scan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('panitia_name');
            $table->string('panitia_pin', 6)->nullable();
            $table->string('peserta_nim')->nullable();
            $table->string('peserta_nama')->nullable();
            $table->string('peserta_prodi')->nullable();
            $table->string('status'); // 'success', 'already', 'not_found', 'error'
            $table->string('message')->nullable();
            $table->timestamp('scanned_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scan_logs');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('pin');
        });
    }
};
