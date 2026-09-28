<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHasParticipatedToMSiswa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_siswa', function (Blueprint $table) {
            if (! Schema::hasColumn('m_siswa', 'has_participated')) {
                $table->tinyInteger('has_participated')->default(0)->after('session_key');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_siswa', function (Blueprint $table) {
            if (Schema::hasColumn('m_siswa', 'has_participated')) {
                $table->dropColumn('has_participated');
            }
        });
    }
}
