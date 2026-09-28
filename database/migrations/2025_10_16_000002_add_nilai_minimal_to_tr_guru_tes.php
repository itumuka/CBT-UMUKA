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
        Schema::table('tr_guru_tes', function (Blueprint $table) {
            if (! Schema::hasColumn('tr_guru_tes', 'nilai_minimal')) {
                $table->integer('nilai_minimal')->default(0)->after('terlambat');
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
        Schema::table('tr_guru_tes', function (Blueprint $table) {
            if (Schema::hasColumn('tr_guru_tes', 'nilai_minimal')) {
                $table->dropColumn('nilai_minimal');
            }
        });
    }
};
