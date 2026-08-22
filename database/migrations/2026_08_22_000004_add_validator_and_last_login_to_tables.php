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
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            }
        });

        Schema::table('payment_verifications', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_verifications', 'validated_by')) {
                $table->string('validated_by')->nullable()->after('nomor_kursi');
            }
            if (!Schema::hasColumn('payment_verifications', 'validated_at')) {
                $table->timestamp('validated_at')->nullable()->after('validated_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_login_at', 'last_login_ip']);
        });

        Schema::table('payment_verifications', function (Blueprint $table) {
            $table->dropColumn(['validated_by', 'validated_at']);
        });
    }
};
