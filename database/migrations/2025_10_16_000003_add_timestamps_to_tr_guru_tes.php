<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

        Schema::table('tr_guru_tes', function (Blueprint $table) {
            // Add nullable timestamps if they don't exist
            if (!Schema::hasColumn('tr_guru_tes', 'created_at') && !Schema::hasColumn('tr_guru_tes', 'updated_at')) {
                $table->timestamps();
            } else {
                if (!Schema::hasColumn('tr_guru_tes', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }
                if (!Schema::hasColumn('tr_guru_tes', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            }
        });
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

        Schema::table('tr_guru_tes', function (Blueprint $table) {
            // Drop timestamps if they exist
            if (Schema::hasColumn('tr_guru_tes', 'created_at') || Schema::hasColumn('tr_guru_tes', 'updated_at')) {
                $table->dropTimestamps();
            }
        });
    }
};
