<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tr_ikut_ujian')) return;

        Schema::table('tr_ikut_ujian', function (Blueprint $table) {
            if (!Schema::hasColumn('tr_ikut_ujian', 'created_at') && !Schema::hasColumn('tr_ikut_ujian', 'updated_at')) {
                $table->timestamps();
            } else {
                if (!Schema::hasColumn('tr_ikut_ujian', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }
                if (!Schema::hasColumn('tr_ikut_ujian', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('tr_ikut_ujian')) return;

        Schema::table('tr_ikut_ujian', function (Blueprint $table) {
            if (Schema::hasColumn('tr_ikut_ujian', 'created_at') || Schema::hasColumn('tr_ikut_ujian', 'updated_at')) {
                $table->dropTimestamps();
            }
        });
    }
};
