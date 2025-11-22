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
        Schema::create('schedule_template', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', \App\Enums\ScheduleType::values());
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('music_template_id')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_template');
    }
};
