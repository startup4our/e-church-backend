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
        Schema::create('schedule_template_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('schedule_template')->onDelete('cascade');
            $table->foreignId('area_id')->constrained('area')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('role')->onDelete('cascade');
            $table->integer('count')->default(1);
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->unique(['template_id', 'area_id', 'role_id']);
            $table->index('template_id');
            $table->index('area_id');
            $table->index('role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_template_role');
    }
};

