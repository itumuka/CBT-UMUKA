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
                if (!Schema::hasColumn('m_siswa', 'token')) {
                    $table->string('token', 5)->unique()->after('email');
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
                if (Schema::hasColumn('m_siswa', 'token')) {
                    $table->dropUnique(['token']);
                    $table->dropColumn('token');
                }
            });
        }
    }
};
