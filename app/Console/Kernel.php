<?php

namespace App\Console;

use App\Jobs\CloseExpiredSchedulesJob;
use App\Jobs\DeleteOldMessagesJob;
use App\Jobs\HandoutsManagerJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new HandoutsManagerJob())->everyFiveMinutes();
        $schedule->job(new CloseExpiredSchedulesJob())->everyFiveMinutes();
        $schedule->job(new DeleteOldMessagesJob())->daily();
        $schedule->job(new \App\Jobs\CheckScheduleRemindersJob())->everyFifteenMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
