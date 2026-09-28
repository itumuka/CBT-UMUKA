<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tr_guru_tes', function (Blueprint $table) {
            if (!Schema::hasColumn('tr_guru_tes', 'id_mapels')) {
                $table->text('id_mapels')->nullable()->after('id_mapel');
            }
        });

        // Migrate existing single id_mapel into id_mapels for existing rows
        try {
            $rows = \Illuminate\Support\Facades\DB::table('tr_guru_tes')->select('id','id_mapel')->get();
            foreach ($rows as $r) {
                if ($r->id_mapel) {
                    \Illuminate\Support\Facades\DB::table('tr_guru_tes')->where('id', $r->id)->update(['id_mapels' => (string)$r->id_mapel]);
                }
            }
        } catch (\Exception $e) {
            // ignore migration errors on some environments
        }
    }

    public function down()
    {
        Schema::table('tr_guru_tes', function (Blueprint $table) {
            if (Schema::hasColumn('tr_guru_tes', 'id_mapels')) {
                $table->dropColumn('id_mapels');
            }
        });
    }
};
