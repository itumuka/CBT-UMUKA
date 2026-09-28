<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('m_soal')) return;

        Schema::table('m_soal', function (Blueprint $table) {
            if (!Schema::hasColumn('m_soal', 'sumber')) {
                $table->string('sumber')->nullable()->after('jenis')->comment('sumber soal: master atau tryout');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('m_soal')) return;

        Schema::table('m_soal', function (Blueprint $table) {
            if (Schema::hasColumn('m_soal', 'sumber')) {
                $table->dropColumn('sumber');
            }
        });
    }
};
