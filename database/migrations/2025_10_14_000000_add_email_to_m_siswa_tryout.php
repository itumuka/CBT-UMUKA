<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('m_siswa_tryout')) {
            return;
        }

        Schema::table('m_siswa_tryout', function (Blueprint $table) {
            if (!Schema::hasColumn('m_siswa_tryout', 'email')) {
                $table->string('email', 255)->nullable()->after('no_hp');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('m_siswa_tryout')) {
            return;
        }

        Schema::table('m_siswa_tryout', function (Blueprint $table) {
            if (Schema::hasColumn('m_siswa_tryout', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};
