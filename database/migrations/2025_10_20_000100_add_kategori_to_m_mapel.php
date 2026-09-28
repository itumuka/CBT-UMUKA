<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('m_mapel')) {
            Schema::table('m_mapel', function (Blueprint $table) {
                if (!Schema::hasColumn('m_mapel', 'kategori')) {
                    $table->string('kategori')->nullable()->after('nama');
                }
            });

            // Backfill existing rows to a safe default 'wajib' if there are any rows
            try {
                DB::table('m_mapel')->whereNull('kategori')->update(['kategori' => 'wajib']);
            } catch (\Exception $e) {
                // If update fails, don't block migration — admin can fix data manually
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('m_mapel')) {
            Schema::table('m_mapel', function (Blueprint $table) {
                if (Schema::hasColumn('m_mapel', 'kategori')) {
                    $table->dropColumn('kategori');
                }
            });
        }
    }
};
