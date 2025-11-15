<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Enums\ChatType;
use App\Models\Message;
use App\Services\Interfaces\IMessageService;
use App\Services\Interfaces\IStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    protected IMessageService $service;
    protected IStorageService $storageService;

    public function __construct(IMessageService $service, IStorageService $storageService)
    {
        $this->service = $service;
        $this->storageService = $storageService;
    }

    public function index()
    {
        try {
            $messages = $this->service->listAll();
            return response()->json([
                'success' => true,
                'data' => $messages
            ]);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'content' => 'required_without:file|string|nullable',
                'file' => 'required_without:content|file|image|max:10240',
                'sent_at' => 'required|date',
                'chat_id' => 'required',
                'user_id' => 'required',
            ]);

            // Verificar se é chat de escala e se está ativa
            $chat = \App\Models\Chat::find($data['chat_id']);
            if ($chat && $chat->chatable_type === ChatType::SCALE->value) {
                $schedule = \App\Models\Schedule::find($chat->chatable_id);
                if ($schedule && $schedule->status !== \App\Enums\ScheduleStatus::ACTIVE) {
                    throw new AppException(
                        ErrorCode::PERMISSION_DENIED,
                        userMessage: 'Não é possível enviar mensagens no chat de uma escala não publicada'
                    );
                }
            }

            $imagePath = null;
            $imageUrl = null;

            // Handle file upload if present
            if ($request->hasFile('file')) {
                Log::info('Message with image upload', [
                    'chat_id' => $request->input('chat_id'),
                    'user_id' => $request->input('user_id')
                ]);

                $file = $request->file('file');
                $chatId = $request->input('chat_id');
                $userId = $request->input('user_id');
                
                // Generate custom filename
                $customName = "chat-{$chatId}-user-{$userId}-" . time();
                
                // Upload image to storage
                $uploadResult = $this->storageService->uploadImage($file, 'chat', $customName);

                if ($uploadResult['success'] && isset($uploadResult['data'])) {
                    $imagePath = $uploadResult['data']['file_path'];
                    $imageUrl = $uploadResult['data']['url'];
                    
                    Log::info('Image uploaded successfully', [
                        'file_path' => $imagePath,
                        'image_url' => $imageUrl
                    ]);
                } else {
                    throw new \Exception('Failed to upload image');
                }
            }

            // Create message with image path
            $messageData = [
                'content' => $request->input('content'),
                'image_path' => $imagePath,
                'sent_at' => $request->input('sent_at'),
                'chat_id' => $request->input('chat_id'),
                'user_id' => $request->input('user_id'),
            ];

            $message = $this->service->create($messageData);
            
            // Add image_url to response
            $messageResponse = $message->toArray();
            $messageResponse['image_url'] = $imageUrl;

            return response()->json([
                'success' => true,
                'data' => $messageResponse
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (\Exception $e) {
            Log::error('Error storing message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function show($id)
    {
        try {
            $message = $this->service->get($id);
            return response()->json([
                'success' => true,
                'data' => $message
            ]);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::RESOURCE_NOT_FOUND,
                userMessage: 'Mensagem não encontrada'
            );
        }
    }

    public function update(Request $request, Message $message)
    {
        try {
            $data = $request->validate([
                'content' => 'sometimes|required|string',
                'image_path' => 'nullable|string',
                'sent_at' => 'sometimes|required|date',
            ]);

            $updatedMessage = $this->service->update($message, $data);
            return response()->json([
                'success' => true,
                'data' => $updatedMessage
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function destroy(Message $message)
    {
        try {
            $this->service->delete($message);
            return response()->json([
                'success' => true,
                'data' => null
            ], 204);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }
}
