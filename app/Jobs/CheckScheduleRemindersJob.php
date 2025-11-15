<?php

namespace App\Jobs;

use App\Enums\ScheduleStatus;
use App\Mail\ScheduleReminderMail;
use App\Models\Schedule;
use App\Models\UserSchedule;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckScheduleRemindersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('CheckScheduleRemindersJob started');

        $now = Carbon::now();
        $emailsSent1Day = 0;
        $emailsSent1Hour = 0;

        // Buscar escalas ACTIVE com start_date entre 24h e 25h no futuro (lembrete de 1 dia)
        $schedules1Day = Schedule::where('status', ScheduleStatus::ACTIVE)
            ->whereBetween('start_date', [
                $now->copy()->addHours(24),
                $now->copy()->addHours(25)
            ])
            ->get();

        Log::info("Found {$schedules1Day->count()} schedules for 1-day reminder");

        foreach ($schedules1Day as $schedule) {
            $participants = UserSchedule::where('schedule_id', $schedule->id)
                ->with('user')
                ->get();

            foreach ($participants as $userSchedule) {
                if ($userSchedule->user && $userSchedule->user->email) {
                    try {
                        Mail::to($userSchedule->user->email)
                            ->send(new ScheduleReminderMail($schedule, '1_day'));
                        $emailsSent1Day++;
                        Log::info("1-day reminder email sent", [
                            'schedule_id' => $schedule->id,
                            'user_id' => $userSchedule->user->id,
                            'email' => $userSchedule->user->email
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Failed to send 1-day reminder email", [
                            'schedule_id' => $schedule->id,
                            'user_id' => $userSchedule->user->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }

        // Buscar escalas ACTIVE com start_date entre 1h e 1h15min no futuro (lembrete de 1 hora)
        $schedules1Hour = Schedule::where('status', ScheduleStatus::ACTIVE)
            ->whereBetween('start_date', [
                $now->copy()->addHour(),
                $now->copy()->addHours(1)->addMinutes(15)
            ])
            ->get();

        Log::info("Found {$schedules1Hour->count()} schedules for 1-hour reminder");

        foreach ($schedules1Hour as $schedule) {
            $participants = UserSchedule::where('schedule_id', $schedule->id)
                ->with('user')
                ->get();

            foreach ($participants as $userSchedule) {
                if ($userSchedule->user && $userSchedule->user->email) {
                    try {
                        Mail::to($userSchedule->user->email)
                            ->send(new ScheduleReminderMail($schedule, '1_hour'));
                        $emailsSent1Hour++;
                        Log::info("1-hour reminder email sent", [
                            'schedule_id' => $schedule->id,
                            'user_id' => $userSchedule->user->id,
                            'email' => $userSchedule->user->email
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Failed to send 1-hour reminder email", [
                            'schedule_id' => $schedule->id,
                            'user_id' => $userSchedule->user->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }

        Log::info('CheckScheduleRemindersJob ended', [
            'emails_sent_1_day' => $emailsSent1Day,
            'emails_sent_1_hour' => $emailsSent1Hour,
            'total_emails_sent' => $emailsSent1Day + $emailsSent1Hour
        ]);
    }
}

