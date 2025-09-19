<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Models\Message;
use App\Services\Interfaces\IMessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    protected IMessageService $service;

    public function __construct(IMessageService $service)
    {
        $this->service = $service;
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
                'content' => 'required|string',
                'image_path' => 'nullable|string',
                'sent_at' => 'required|date',
                'chat_id' => 'required',
                'user_id' => 'required',
            ]);

            $message = $this->service->create($request->toArray());
            return response()->json([
                'success' => true,
                'data' => $message
            ], 201);
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
