<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_schedule', function (Blueprint $table) {
            $table->id(); // auto-increment
            $table->enum('status', ['confirmed', 'swap_requested'])->default('confirmed');
            $table->timestamps();

            // Indexes
            $table->index('schedule_id');
            $table->index('user_id');

            // Foreign keys
            $table->foreignId('schedule_id')->references('id')->on('schedule')->onDelete('cascade');
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_schedule');
    }
};
