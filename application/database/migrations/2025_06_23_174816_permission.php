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
        Schema::create('permission', function (Blueprint $table){
            $table->id();
            // Relação com users
            $table->foreignId('user_id')
                    ->constrained('users', 'id')
                    ->onDelete('cascade');
            // Scale CRUD
            $table->boolean('create_scale')->default(false);
            $table->boolean('read_scale')->default(false);
            $table->boolean('update_scale')->default(false);
            $table->boolean('delete_scale')->default(false);
            // Music CRUD
            $table->boolean('create_music')->default(false);
            $table->boolean('read_music')->default(false);
            $table->boolean('update_music')->default(false);
            $table->boolean('delete_music')->default(false);
            // Others
            $table->boolean('manage_users')->default(true);
            $table->boolean('manage_church_settings')->default(true);
            $table->boolean('manage_app_settings')->default(true);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission');
    }
};
