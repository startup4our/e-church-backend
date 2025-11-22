<?php

namespace App\Jobs;

use App\Services\Interfaces\IPushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $deviceToken;
    public string $title;
    public string $body;
    public ?array $data;
    public ?string $context;
    public ?int $userId;

    /**
     * Create a new job instance.
     *
     * @param string $deviceToken Token FCM do dispositivo
     * @param string $title Título da notificação
     * @param string $body Corpo da notificação
     * @param array|null $data Dados adicionais (opcional)
     * @param string|null $context Contexto para logging (opcional)
     * @param int|null $userId ID do usuário (opcional, para logging)
     */
    public function __construct(
        string $deviceToken,
        string $title,
        string $body,
        ?array $data = null,
        ?string $context = null,
        ?int $userId = null
    ) {
        $this->deviceToken = $deviceToken;
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
        $this->context = $context;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(IPushNotificationService $pushNotificationService): void
    {
        try {
            $result = $pushNotificationService->sendToDevice(
                $this->deviceToken,
                $this->title,
                $this->body,
                $this->data
            );

            if ($result['success']) {
                Log::info('Push notification sent successfully', [
                    'user_id' => $this->userId,
                    'context' => $this->context,
                    'message_id' => $result['message_id'] ?? null,
                ]);
            } else {
                Log::warning('Push notification failed', [
                    'user_id' => $this->userId,
                    'context' => $this->context,
                    'error' => $result['message'] ?? 'Unknown error',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send push notification', [
                'user_id' => $this->userId,
                'context' => $this->context,
                'error' => $e->getMessage(),
            ]);
            
            // Re-throw para que o Laravel possa tentar novamente se configurado
            throw $e;
        }
    }
}

