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
        // Drop the legacy token_enabled column if it exists
        if (Schema::hasTable('m_siswa') && Schema::hasColumn('m_siswa', 'token_enabled')) {
            Schema::table('m_siswa', function (Blueprint $table) {
                $table->dropColumn('token_enabled');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('m_siswa') && ! Schema::hasColumn('m_siswa', 'token_enabled')) {
            Schema::table('m_siswa', function (Blueprint $table) {
                $table->tinyInteger('token_enabled')->default(1)->after('status_akun');
            });
        }
    }
};
