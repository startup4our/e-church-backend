<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permission', function (Blueprint $table) {
            $table->boolean('create_chat')->default(false);
            $table->boolean('read_chat')->default(false);
            $table->boolean('update_chat')->default(false);
            $table->boolean('delete_chat')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('permission', function (Blueprint $table) {
            $table->dropColumn(['create_chat', 'read_chat', 'update_chat', 'delete_chat']);
        });
    }
};
