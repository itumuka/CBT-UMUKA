<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('m_admin')) {
            return;
        }

        Schema::table('m_admin', function (Blueprint $table) {
            if (!Schema::hasColumn('m_admin', 'email')) {
                $table->string('email', 255)->nullable()->after('username');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('m_admin')) {
            return;
        }

        Schema::table('m_admin', function (Blueprint $table) {
            if (Schema::hasColumn('m_admin', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};
