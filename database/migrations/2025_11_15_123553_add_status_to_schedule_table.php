<?php

use App\Enums\ScheduleStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schedule', function (Blueprint $table) {
            $table->enum('status', ScheduleStatus::values())->default(ScheduleStatus::DRAFT->value)->after('approved');
        });

        // Atualizar registros existentes que já têm participantes para ACTIVE
        DB::statement("
            UPDATE schedule 
            SET status = '" . ScheduleStatus::ACTIVE->value . "' 
            WHERE id IN (
                SELECT DISTINCT schedule_id 
                FROM user_schedule
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
