<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tr_guru_tes', 'mode')) {
            Schema::table('tr_guru_tes', function (Blueprint $table) {
                $table->enum('mode', ['cbt', 'tryout'])->default('cbt')->after('jenis');
            });
        }

        // Backfill: ujian bernama TRYOUT masuk mode tryout, sisanya cbt
        DB::table('tr_guru_tes')
            ->where('nama_ujian', 'TRYOUT')
            ->update(['mode' => 'tryout']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('tr_guru_tes', 'mode')) {
            Schema::table('tr_guru_tes', function (Blueprint $table) {
                $table->dropColumn('mode');
            });
        }
    }
};
