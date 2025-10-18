<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cria a tabela primeiro, sem FKs
        Schema::create('invite', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->unsignedBigInteger('area_id');
            $table->unsignedBigInteger('church_id');
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('token', 100)->unique();
            $table->boolean('used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        // Agora adiciona as chaves estrangeiras separadamente
        Schema::table('invite', function (Blueprint $table) {
            if (Schema::hasTable('area')) {
                $table->foreign('area_id')
                    ->references('id')
                    ->on('area')
                    ->onDelete('cascade');
            }

            if (Schema::hasTable('church')) {
                $table->foreign('church_id')
                    ->references('id')
                    ->on('church')
                    ->onDelete('cascade');
            }

            if (Schema::hasTable('role')) {
                $table->foreign('role_id')
                    ->references('id')
                    ->on('role')
                    ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invite');
    }
};
