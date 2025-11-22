<?php

namespace App\Services\Interfaces;

interface IPushNotificationService
{
    /**
     * Envia uma notificação push para um único dispositivo
     *
     * @param string $deviceToken Token FCM do dispositivo
     * @param string $title Título da notificação
     * @param string $body Corpo da notificação
     * @param array|null $data Dados adicionais (opcional)
     * @return array
     */
    public function sendToDevice(string $deviceToken, string $title, string $body, ?array $data = null): array;

    /**
     * Envia uma notificação push para múltiplos dispositivos
     *
     * @param array $deviceTokens Array de tokens FCM
     * @param string $title Título da notificação
     * @param string $body Corpo da notificação
     * @param array|null $data Dados adicionais (opcional)
     * @return array
     */
    public function sendToMultipleDevices(array $deviceTokens, string $title, string $body, ?array $data = null): array;

    /**
     * Envia uma notificação push para um tópico
     *
     * @param string $topic Nome do tópico
     * @param string $title Título da notificação
     * @param string $body Corpo da notificação
     * @param array|null $data Dados adicionais (opcional)
     * @return array
     */
    public function sendToTopic(string $topic, string $title, string $body, ?array $data = null): array;

    /**
     * Envia uma notificação push para uma condição
     *
     * @param string $condition Condição FCM (ex: "'dogs' in topics && 'cats' in topics")
     * @param string $title Título da notificação
     * @param string $body Corpo da notificação
     * @param array|null $data Dados adicionais (opcional)
     * @return array
     */
    public function sendToCondition(string $condition, string $title, string $body, ?array $data = null): array;
}

