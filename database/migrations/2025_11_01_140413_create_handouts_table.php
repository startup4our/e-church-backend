<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handouts', function (Blueprint $table) {
            $table->id();

            // Church relationship
            $table->foreignId('church_id')->constrained('church')->onDelete('cascade');

            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('area_id')->nullable();

            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();

            $table->string('priority')->default('normal');
            $table->string('status')->default('P');

            $table->string('link_name')->nullable();
            $table->string('link_url')->nullable();
            $table->string('image_url')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handouts');
    }
};
