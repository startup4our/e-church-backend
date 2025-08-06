<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recordings', function (Blueprint $table) {
            $table->id();

            $table->string('path'); // file path or URL
            $table->enum('type', ['solo', 'soprano', 'contralto', 'tenor']);
            $table->string('description')->nullable();

            // Foreign key to songs (assuming songs.id is string)
            $table->foreignId('song_id')
                  ->constrained('song', 'id')
                  ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recordings');
    }
};
