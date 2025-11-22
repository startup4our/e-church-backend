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
        Schema::create('schedule_batch', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('total_schedules');
            $table->integer('created_schedules')->default(0);
            $table->integer('failed_schedules')->default(0);
            $table->enum('recurrence', ['diária', 'semanal', 'quinzenal', 'mensal']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->foreignId('template_id')->nullable()->constrained('schedule_template')->onDelete('set null');
            $table->foreignId('user_creator')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index('user_creator');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_batch');
    }
};

