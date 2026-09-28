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
        if (!Schema::hasTable('m_soal')) {
            return;
        }

        Schema::table('m_soal', function (Blueprint $table) {
            if (!Schema::hasColumn('m_soal', 'jenis')) {
                $table->string('jenis')->nullable()->after('tipe_file')->comment('tipe soal: teks atau listening');
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
        if (!Schema::hasTable('m_soal')) {
            return;
        }

        Schema::table('m_soal', function (Blueprint $table) {
            if (Schema::hasColumn('m_soal', 'jenis')) {
                $table->dropColumn('jenis');
            }
        });
    }
};
