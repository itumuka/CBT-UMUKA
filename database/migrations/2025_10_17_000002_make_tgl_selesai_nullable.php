<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Use raw SQL to avoid dependency on doctrine/dbal for column changes
        DB::statement("ALTER TABLE `tr_ikut_ujian` MODIFY `tgl_selesai` DATETIME NULL;");
    }

    public function down()
    {
        // Revert to NOT NULL with a default minimal timestamp to avoid errors
        DB::statement("ALTER TABLE `tr_ikut_ujian` MODIFY `tgl_selesai` DATETIME NOT NULL;");
    }
};
