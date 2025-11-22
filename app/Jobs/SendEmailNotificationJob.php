<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $email;
    public Mailable $mailable;
    public ?string $context;

    /**
     * Create a new job instance.
     *
     * @param string $email Email do destinatário
     * @param Mailable $mailable Instância do Mailable a ser enviado
     * @param string|null $context Contexto para logging (opcional)
     */
    public function __construct(string $email, Mailable $mailable, ?string $context = null)
    {
        $this->email = $email;
        $this->mailable = $mailable;
        $this->context = $context;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->email)->send($this->mailable);
            
            Log::info('Email notification sent successfully', [
                'email' => $this->email,
                'context' => $this->context,
                'mailable' => get_class($this->mailable),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'email' => $this->email,
                'context' => $this->context,
                'mailable' => get_class($this->mailable),
                'error' => $e->getMessage(),
            ]);
            
            // Re-throw para que o Laravel possa tentar novamente se configurado
            throw $e;
        }
    }
}

