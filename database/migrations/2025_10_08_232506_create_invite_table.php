<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cria a tabela de convites
        Schema::create('invite', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->unsignedBigInteger('church_id');
            $table->string('token', 100)->unique();
            $table->boolean('used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        // Adiciona chave estrangeira para church
        Schema::table('invite', function (Blueprint $table) {
            if (Schema::hasTable('church')) {
                $table->foreign('church_id')
                    ->references('id')
                    ->on('church')
                    ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invite');
    }
};
