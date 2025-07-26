<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedule', function (Blueprint $table){
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('local')->nullable();
            $table->dateTime('date_time');
            $table->string('observation')->nullable();
            $table->enum('type', ['louvor', 'geral']);
            $table->boolean('aproved')->default(false);
            $table->foreignId('user_creator')
                    ->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule');
    }
};
