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
        // Step 1: remove duplicate rows that have same (level, kon_id) keeping smallest id
        try {
            $duplicates = DB::select(
                "SELECT level, kon_id, GROUP_CONCAT(id ORDER BY id ASC) as ids, COUNT(*) as cnt
                 FROM m_admin
                 WHERE kon_id IS NOT NULL
                 GROUP BY level, kon_id
                 HAVING cnt > 1"
            );

            foreach ($duplicates as $dup) {
                $ids = explode(',', $dup->ids);
                // keep first (smallest) id and delete the rest
                $keep = array_shift($ids);
                if (!empty($ids)) {
                    DB::table('m_admin')->whereIn('id', $ids)->delete();
                }
            }
        } catch (\Exception $e) {
            // If the select fails (table missing etc.), continue to attempt index creation
        }

        // Step 2: add unique index without requiring doctrine/dbal
        if (Schema::hasTable('m_admin')) {
            // Check via INFORMATION_SCHEMA (works on MySQL/MariaDB). If not available, we attempt creation and handle errors.
            $indexExists = false;
            try {
                $dbName = DB::getDatabaseName();
                $res = DB::select("SELECT COUNT(1) as cnt FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?", [$dbName, 'm_admin', 'm_admin_level_kon_id_unique']);
                if (!empty($res) && isset($res[0]->cnt) && $res[0]->cnt > 0) {
                    $indexExists = true;
                }
            } catch (\Exception $e) {
                // ignore - INFORMATION_SCHEMA might not be accessible; we'll try to create index directly below
            }

            if (! $indexExists) {
                try {
                    Schema::table('m_admin', function (Blueprint $table) {
                        $table->unique(['level','kon_id'], 'm_admin_level_kon_id_unique');
                    });
                } catch (\Exception $e) {
                    // If index creation fails (e.g., duplicates still exist), throw to notify the operator.
                    throw $e;
                }
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
        if (Schema::hasTable('m_admin')) {
            Schema::table('m_admin', function (Blueprint $table) {
                $idx = 'm_admin_level_kon_id_unique';
                try {
                    $table->dropUnique($idx);
                } catch (\Exception $e) {
                    // ignore
                }
            });
        }
    }
};
