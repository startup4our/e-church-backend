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
        Schema::create('schedule_batch_schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('schedule_batch')->onDelete('cascade');
            $table->foreignId('schedule_id')->constrained('schedule')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['batch_id', 'schedule_id']);
            $table->index('batch_id');
            $table->index('schedule_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_batch_schedule');
    }
};

