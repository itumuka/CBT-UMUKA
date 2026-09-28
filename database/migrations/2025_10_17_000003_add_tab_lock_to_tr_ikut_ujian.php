<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tr_ikut_ujian', function (Blueprint $table) {
            $table->string('active_tab_id', 64)->nullable()->after('flag_cheat');
            $table->timestamp('active_tab_ping')->nullable()->after('active_tab_id');
        });
    }

    public function down()
    {
        Schema::table('tr_ikut_ujian', function (Blueprint $table) {
            $table->dropColumn(['active_tab_id','active_tab_ping']);
        });
    }
};
