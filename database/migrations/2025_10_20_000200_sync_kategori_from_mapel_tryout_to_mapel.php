<?php

use Illuminate\Database\Migrations\Migration;
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
        if (DB::getSchemaBuilder()->hasTable('m_mapel') && DB::getSchemaBuilder()->hasTable('m_mapel_tryout')) {
            try {
                $tryouts = DB::table('m_mapel_tryout')->get();
                foreach ($tryouts as $t) {
                    $name = trim(strtolower($t->nama ?? ''));
                    if ($name === '') continue;

                    DB::table('m_mapel')
                        ->whereRaw('LOWER(TRIM(nama)) = ?', [$name])
                        ->update(['kategori' => $t->kategori]);
                }
            } catch (\Exception $e) {
                // don't block migration; admin can review later
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
        // No destructive rollback for data sync; leave kategori values intact
    }
};
