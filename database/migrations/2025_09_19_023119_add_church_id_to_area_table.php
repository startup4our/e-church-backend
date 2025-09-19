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
        Schema::table('area', function (Blueprint $table) {
            $table->foreignId('church_id')->constrained('church')->onDelete('cascade');
            $table->dropUnique(['name']);
            $table->unique(['name', 'church_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('area', function (Blueprint $table) {
            $table->dropUnique(['name', 'church_id']);
            $table->unique('name');
            $table->dropForeign(['church_id']);
            $table->dropColumn('church_id');
        });
    }
};
