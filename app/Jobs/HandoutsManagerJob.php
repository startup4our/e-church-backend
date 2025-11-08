<?php

namespace App\Jobs;

use App\Enums\HandoutStatus;
use App\Models\Handout;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class HandoutsManagerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Handout $handout;

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
        // If this logic gets too complex, consider moving it to a service class
        Log::info('Manager Handout Job started');

        $now = Carbon::now();

        // Activate handouts that have reached their start date
        Handout::where('start_date', '<=', $now)
            ->where('status', '=', 'P') 
            ->update(['status' => HandoutStatus::ACTIVE]);

        // Inactivate handouts that have passed their end date
        Handout::where('end_date', '<=', $now)
            ->where('status', '=', 'A')
            ->update(['status' => HandoutStatus::INACTIVE]);

        Log::info('Manager Handout Job ended');
    }
}
