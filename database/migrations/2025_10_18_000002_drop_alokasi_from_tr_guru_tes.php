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
        if (Schema::hasColumn('tr_guru_tes', 'alokasi')) {
            Schema::table('tr_guru_tes', function (Blueprint $table) {
                $table->dropColumn('alokasi');
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
        if (! Schema::hasColumn('tr_guru_tes', 'alokasi')) {
            Schema::table('tr_guru_tes', function (Blueprint $table) {
                $table->integer('alokasi')->nullable()->after('waktu')->comment('Alokasi khusus (menit) - optional');
            });
        }
    }
};
