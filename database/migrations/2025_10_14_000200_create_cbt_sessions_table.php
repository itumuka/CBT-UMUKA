<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cbt_sessions')) {
            return;
        }

        Schema::create('cbt_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // 'cbt' or 'tryout'
            $table->string('name');
            $table->boolean('active')->default(false);
            $table->timestamps();
        });

        // Insert default sessions
        \Illuminate\Support\Facades\DB::table('cbt_sessions')->insert([
            ['key' => 'cbt', 'name' => 'CBT', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'tryout', 'name' => 'TRYOUT', 'active' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('cbt_sessions');
    }
};
