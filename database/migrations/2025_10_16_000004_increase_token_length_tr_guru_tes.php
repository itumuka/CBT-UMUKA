<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
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
        if (!Schema::hasTable('tr_guru_tes')) {
            return;
        }

        // Use raw statement to avoid needing doctrine/dbal for column change
        DB::statement("ALTER TABLE `tr_guru_tes` MODIFY `token` VARCHAR(16) NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('tr_guru_tes')) {
            return;
        }

        // Revert back to varchar(5) if the column exists
        DB::statement("ALTER TABLE `tr_guru_tes` MODIFY `token` VARCHAR(5) NOT NULL");
    }
};
