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
            if (!Schema::hasColumn('m_siswa_tryout', 'session_key')) {
                $table->string('session_key', 50)->nullable()->after('email');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('m_siswa_tryout')) {
            return;
        }
        Schema::table('m_siswa_tryout', function (Blueprint $table) {
            if (Schema::hasColumn('m_siswa_tryout','session_key')) {
                $table->dropColumn('session_key');
            }
        });
    }
};
