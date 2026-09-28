<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('m_siswa')) {
            Schema::table('m_siswa', function (Blueprint $table) {
                if (!Schema::hasColumn('m_siswa', 'sekolah')) {
                    $table->string('sekolah')->nullable();
                }
                if (!Schema::hasColumn('m_siswa', 'no_hp')) {
                    $table->string('no_hp')->nullable();
                }
                if (!Schema::hasColumn('m_siswa', 'email')) {
                    $table->string('email')->nullable()->unique();
                }
                if (!Schema::hasColumn('m_siswa', 'mapel_wajib')) {
                    $table->text('mapel_wajib')->nullable();
                }
                if (!Schema::hasColumn('m_siswa', 'mapel_pilihan')) {
                    $table->text('mapel_pilihan')->nullable();
                }
                if (!Schema::hasColumn('m_siswa', 'session_key')) {
                    $table->string('session_key')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('m_siswa')) {
            Schema::table('m_siswa', function (Blueprint $table) {
                if (Schema::hasColumn('m_siswa', 'sekolah')) {
                    $table->dropColumn('sekolah');
                }
                if (Schema::hasColumn('m_siswa', 'no_hp')) {
                    $table->dropColumn('no_hp');
                }
                if (Schema::hasColumn('m_siswa', 'email')) {
                    $table->dropUnique(['email']);
                    $table->dropColumn('email');
                }
                if (Schema::hasColumn('m_siswa', 'mapel_wajib')) {
                    $table->dropColumn('mapel_wajib');
                }
                if (Schema::hasColumn('m_siswa', 'mapel_pilihan')) {
                    $table->dropColumn('mapel_pilihan');
                }
                if (Schema::hasColumn('m_siswa', 'session_key')) {
                    $table->dropColumn('session_key');
                }
            });
        }
    }
};
