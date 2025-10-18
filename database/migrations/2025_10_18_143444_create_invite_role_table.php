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
        Schema::create('invite_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invite_id')->constrained('invite')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('role')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['invite_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invite_role');
    }
};

