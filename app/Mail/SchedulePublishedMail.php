<?php

namespace App\Mail;

use App\Models\Schedule;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SchedulePublishedMail extends Mailable
{
    use Queueable, SerializesModels;

    public int $scheduleId;

    /**
     * Cria uma nova instância de SchedulePublishedMail
     */
    public function __construct(Schedule $schedule)
    {
        $this->scheduleId = $schedule->id;
    }

    /**
     * Monta o e-mail
     */
    public function build()
    {
        // Recarregar o Schedule do banco
        $schedule = Schedule::findOrFail($this->scheduleId);
        
        $url = env('FRONTEND_URL') . '/schedules/' . $schedule->id;
        
        // Formatar data: "dia DD/MM/YYYY às HH:mm"
        $formattedDate = $schedule->start_date->format('d/m/Y \à\s H:i');

        return $this->subject('Você foi escalado!')
            ->view('emails.schedule-published')
            ->with([
                'schedule' => $schedule,
                'url' => $url,
                'formattedDate' => $formattedDate,
            ]);
    }
}

