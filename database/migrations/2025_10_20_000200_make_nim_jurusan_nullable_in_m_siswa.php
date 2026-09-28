<?php

use Illuminate\Database\Migrations\Migration;
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
        if (Schema::hasTable('m_siswa')) {
            try {
                DB::statement("ALTER TABLE `m_siswa` MODIFY `nim` VARCHAR(255) NULL;");
            } catch (\Exception $e) {
                // ignore if column doesn't exist or modification fails
            }

            try {
                DB::statement("ALTER TABLE `m_siswa` MODIFY `jurusan` VARCHAR(255) NULL;");
            } catch (\Exception $e) {
                // ignore
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
        if (Schema::hasTable('m_siswa')) {
            try {
                DB::statement("ALTER TABLE `m_siswa` MODIFY `nim` VARCHAR(255) NOT NULL DEFAULT '';");
            } catch (\Exception $e) {
                // ignore
            }

            try {
                DB::statement("ALTER TABLE `m_siswa` MODIFY `jurusan` VARCHAR(255) NOT NULL DEFAULT '';");
            } catch (\Exception $e) {
                // ignore
            }
        }
    }
};
