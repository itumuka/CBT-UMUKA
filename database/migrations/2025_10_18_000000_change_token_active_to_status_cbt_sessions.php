<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('cbt_sessions')) return;

        if (Schema::hasColumn('cbt_sessions', 'token_active')) {
            // rename boolean token_active to token_status (tinyInteger)
            Schema::table('cbt_sessions', function (Blueprint $table) {
                $table->tinyInteger('token_status')->default(0)->after('active');
            });

            // migrate values: token_active true -> 1 (real), false -> 0 (off)
            DB::table('cbt_sessions')->where('token_active', true)->update(['token_status' => 1]);

            Schema::table('cbt_sessions', function (Blueprint $table) {
                if (Schema::hasColumn('cbt_sessions', 'token_active')) {
                    $table->dropColumn('token_active');
                }
            });
        } elseif (!Schema::hasColumn('cbt_sessions', 'token_status')) {
            Schema::table('cbt_sessions', function (Blueprint $table) {
                $table->tinyInteger('token_status')->default(0)->after('active');
            });
        }
    }

    public function down()
    {
        if (!Schema::hasTable('cbt_sessions')) return;

        if (Schema::hasColumn('cbt_sessions', 'token_status')) {
            Schema::table('cbt_sessions', function (Blueprint $table) {
                $table->boolean('token_active')->default(false)->after('active');
            });

            DB::table('cbt_sessions')->where('token_status', '>', 0)->update(['token_active' => true]);

            Schema::table('cbt_sessions', function (Blueprint $table) {
                if (Schema::hasColumn('cbt_sessions', 'token_status')) {
                    $table->dropColumn('token_status');
                }
            });
        }
    }
};
