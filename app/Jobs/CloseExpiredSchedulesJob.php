<?php

namespace App\Jobs;

use App\Enums\ScheduleStatus;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CloseExpiredSchedulesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('CloseExpiredSchedulesJob started');

        $now = Carbon::now();

        // Buscar escalas ACTIVE com end_date passada
        $expiredSchedules = Schedule::where('status', ScheduleStatus::ACTIVE)
            ->where('end_date', '<=', $now)
            ->get();

        $count = $expiredSchedules->count();
        Log::info("Found {$count} expired schedules to close");

        // Fechar escalas atualizando status para COMPLETE
        if ($count > 0) {
            Schedule::where('status', ScheduleStatus::ACTIVE)
                ->where('end_date', '<=', $now)
                ->update(['status' => ScheduleStatus::COMPLETE]);

            Log::info("Closed {$count} expired schedules", [
                'schedule_ids' => $expiredSchedules->pluck('id')->toArray()
            ]);
        }

        Log::info('CloseExpiredSchedulesJob ended');
    }
}

