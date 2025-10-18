<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['A', 'I', 'WA', 'R'])->default('WA');
            $table->foreignId('church_id')->nullable()->constrained('church')->onDelete('cascade');
        });

    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['church_id']);
            $table->dropColumn(['status', 'church_id']);
        });
    }
};

