<?php

namespace App\Mail;

use App\Models\Schedule;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ScheduleReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public int $scheduleId;
    public string $reminderType; // '1_day' ou '1_hour'
    public string $message;

    /**
     * Cria uma nova instância de ScheduleReminderMail
     */
    public function __construct(Schedule $schedule, string $reminderType)
    {
        $this->scheduleId = $schedule->id;
        $this->reminderType = $reminderType;
        
        // Definir mensagem baseada no tipo (usar o schedule antes de serializar)
        $formattedDate = $schedule->start_date->format('d/m/Y \à\s H:i');
        
        if ($reminderType === '1_day') {
            $this->message = "Lembrete: Você tem uma escala amanhã - {$schedule->name}, dia {$formattedDate}";
        } else {
            $this->message = "Lembrete: Sua escala começa em 1 hora - {$schedule->name}, dia {$formattedDate}";
        }
    }

    /**
     * Monta o e-mail
     */
    public function build()
    {
        // Recarregar o Schedule do banco
        $schedule = Schedule::findOrFail($this->scheduleId);
        
        $url = env('FRONTEND_URL') . '/schedules/' . $schedule->id;
        $formattedDate = $schedule->start_date->format('d/m/Y \à\s H:i');

        return $this->subject($this->message)
            ->view('emails.schedule-reminder')
            ->with([
                'schedule' => $schedule,
                'url' => $url,
                'formattedDate' => $formattedDate,
                'reminderType' => $this->reminderType,
                'message' => $this->message,
            ]);
    }
}

