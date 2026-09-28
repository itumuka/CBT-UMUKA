<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('tr_ikut_ujian')) return;

        Schema::table('tr_ikut_ujian', function (Blueprint $table) {
            if (! Schema::hasColumn('tr_ikut_ujian', 'cheat_count')) {
                $table->integer('cheat_count')->default(0)->after('nilai_bobot');
            }
            if (! Schema::hasColumn('tr_ikut_ujian', 'flag_cheat')) {
                $table->boolean('flag_cheat')->default(false)->after('cheat_count');
            }
        });
    }

    public function down()
    {
        if (! Schema::hasTable('tr_ikut_ujian')) return;

        Schema::table('tr_ikut_ujian', function (Blueprint $table) {
            if (Schema::hasColumn('tr_ikut_ujian', 'flag_cheat')) {
                $table->dropColumn('flag_cheat');
            }
            if (Schema::hasColumn('tr_ikut_ujian', 'cheat_count')) {
                $table->dropColumn('cheat_count');
            }
        });
    }
};
