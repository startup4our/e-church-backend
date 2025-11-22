<?php

namespace App\Services;

use App\Services\Interfaces\IPushNotificationService;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class PushNotificationService implements IPushNotificationService
{
    private $messaging;

    public function __construct()
    {
        $this->initializeFirebase();
    }

    private function initializeFirebase(): void
    {
        $projectId = config('firebase.project_id');
        $privateKeyId = config('firebase.private_key_id');
        $privateKey = config('firebase.private_key');
        $clientEmail = config('firebase.client_email');
        $clientId = config('firebase.client_id');
        $authUri = config('firebase.auth_uri');
        $tokenUri = config('firebase.token_uri');
        $authProviderX509CertUrl = config('firebase.auth_provider_x509_cert_url');
        $clientX509CertUrl = config('firebase.client_x509_cert_url');

        if (empty($projectId) || empty($privateKey) || empty($clientEmail)) {
            throw new \Exception("Firebase credentials not properly configured. Please check your .env file.");
        }

        $serviceAccount = [
            'type' => 'service_account',
            'project_id' => $projectId,
            'private_key_id' => $privateKeyId,
            'private_key' => $privateKey,
            'client_email' => $clientEmail,
            'client_id' => $clientId,
            'auth_uri' => $authUri,
            'token_uri' => $tokenUri,
            'auth_provider_x509_cert_url' => $authProviderX509CertUrl,
            'client_x509_cert_url' => $clientX509CertUrl,
        ];

        $factory = (new Factory)->withServiceAccount($serviceAccount);
        $this->messaging = $factory->createMessaging();
    }

    public function sendToDevice(string $deviceToken, string $title, string $body, ?array $data = null): array
    {
        try {
            $notification = Notification::create($title, $body);
            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification($notification);

            if ($data) {
                $message = $message->withData($data);
            }

            $result = $this->messaging->send($message);

            Log::info('Push notification sent successfully', [
                'device_token' => substr($deviceToken, 0, 20) . '...',
                'message_id' => $result,
            ]);

            return [
                'success' => true,
                'message' => 'Notification sent successfully',
                'message_id' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send push notification', [
                'device_token' => substr($deviceToken, 0, 20) . '...',
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    public function sendToMultipleDevices(array $deviceTokens, string $title, string $body, ?array $data = null): array
    {
        try {
            $notification = Notification::create($title, $body);
            $messages = [];

            foreach ($deviceTokens as $token) {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification($notification);

                if ($data) {
                    $message = $message->withData($data);
                }

                $messages[] = $message;
            }

            $results = $this->messaging->sendAll($messages);

            $successCount = $results->successes()->count();
            $failureCount = $results->failures()->count();

            Log::info('Batch push notifications sent', [
                'total' => count($deviceTokens),
                'success' => $successCount,
                'failures' => $failureCount,
            ]);

            return [
                'success' => true,
                'message' => "Sent {$successCount} notifications successfully, {$failureCount} failed",
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'results' => $results,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send batch push notifications', [
                'device_count' => count($deviceTokens),
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send batch notifications: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    public function sendToTopic(string $topic, string $title, string $body, ?array $data = null): array
    {
        try {
            $notification = Notification::create($title, $body);
            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification($notification);

            if ($data) {
                $message = $message->withData($data);
            }

            $result = $this->messaging->send($message);

            Log::info('Push notification sent to topic', [
                'topic' => $topic,
                'message_id' => $result,
            ]);

            return [
                'success' => true,
                'message' => 'Notification sent to topic successfully',
                'message_id' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send push notification to topic', [
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send notification to topic: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    public function sendToCondition(string $condition, string $title, string $body, ?array $data = null): array
    {
        try {
            $notification = Notification::create($title, $body);
            $message = CloudMessage::withTarget('condition', $condition)
                ->withNotification($notification);

            if ($data) {
                $message = $message->withData($data);
            }

            $result = $this->messaging->send($message);

            Log::info('Push notification sent to condition', [
                'condition' => $condition,
                'message_id' => $result,
            ]);

            return [
                'success' => true,
                'message' => 'Notification sent to condition successfully',
                'message_id' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send push notification to condition', [
                'condition' => $condition,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send notification to condition: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }
}

