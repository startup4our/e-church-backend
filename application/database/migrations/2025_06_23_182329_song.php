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
        Schema::create('song', function (Blueprint $table) {
            $table->id();
            $table->string('cover_path'); // path to cover image
            $table->string('name');
            $table->string('artist');
            $table->string('spotify_id')->nullable();
            $table->string('preview_url')->nullable();
            $table->integer('duration'); // duration in seconds (or ms, depende do padrão que escolher)
            $table->string('album')->nullable();
            $table->string('spotify_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};
