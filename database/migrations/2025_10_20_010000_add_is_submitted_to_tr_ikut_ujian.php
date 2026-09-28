<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsSubmittedToTrIkutUjian extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tr_ikut_ujian', function (Blueprint $table) {
            if (! Schema::hasColumn('tr_ikut_ujian', 'is_submitted')) {
                $table->tinyInteger('is_submitted')->default(0)->after('tgl_selesai');
                $table->index(['id_user','id_tes','is_submitted'], 'ikut_user_tes_submitted_idx');
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
        Schema::table('tr_ikut_ujian', function (Blueprint $table) {
            if (Schema::hasColumn('tr_ikut_ujian', 'is_submitted')) {
                $table->dropIndex('ikut_user_tes_submitted_idx');
                $table->dropColumn('is_submitted');
            }
        });
    }
}
