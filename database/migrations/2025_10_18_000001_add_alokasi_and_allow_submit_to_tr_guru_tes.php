<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tr_guru_tes', function (Blueprint $table) {
            if (!Schema::hasColumn('tr_guru_tes', 'alokasi')) {
                $table->integer('alokasi')->nullable()->after('waktu')->comment('Alokasi khusus (menit) - optional');
            }
            if (!Schema::hasColumn('tr_guru_tes', 'allow_submit_after')) {
                $table->integer('allow_submit_after')->nullable()->after('alokasi')->comment('Boleh submit beberapa menit setelah terlambat');
            }
        });
    }

    public function down()
    {
        Schema::table('tr_guru_tes', function (Blueprint $table) {
            if (Schema::hasColumn('tr_guru_tes', 'allow_submit_after')) {
                $table->dropColumn('allow_submit_after');
            }
            if (Schema::hasColumn('tr_guru_tes', 'alokasi')) {
                $table->dropColumn('alokasi');
            }
        });
    }
};
