<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('impersonation_logs', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();  // email yang dicoba
            $table->unsignedBigInteger('user_id')->nullable();  // ID siswa yang diimpersonasi
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->enum('status', ['success', 'failed'])->default('failed');
            $table->string('reason')->nullable();  // alasan gagal atau informasi tambahan
            $table->timestamps();

            $table->index('email');
            $table->index(['user_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('impersonation_logs');
    }
};