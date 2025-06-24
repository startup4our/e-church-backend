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
        Schema::create('date_exceptions', function (Blueprint $table) {
            $table->id();

            $table->date('exception_date'); // specific calendar date
            $table->enum('shift', ['morning', 'afternoon', 'night']);
            $table->text('justification')->nullable();

            $table->foreignId('user_id')->constrained('users', 'id')->onDelete('cascade');

            $table->timestamps();

            $table->unique(['user_id', 'exception_date', 'shift']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('date_exceptions');
    }
};
