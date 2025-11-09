<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Enums\ChatType;
use App\Services\Interfaces\IChatService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    protected IChatService $service;
    protected PermissionService $permissionService;

    public function __construct(IChatService $service, PermissionService $permissionService)
    {
        $this->service = $service;
        $this->permissionService = $permissionService;
    }

    public function index()
    {
        Log::info("Request to list all chats");

        return response()->json([
            'success' => true,
            'data' => $this->service->getAll()
        ]);
    }

    public function show($id, Request $request)
    {
        Log::info("Request to show chat", ['chat_id' => $id]);

        try {
            $userId = $request->input('user_id');
            
            // Verificar se usuário tem acesso ao chat baseado nas áreas
            if ($userId && !$this->service->userHasAccessToChat($userId, $id)) {
                Log::warning("User [{$userId}] tried to access chat [{$id}] but doesn't belong to the area/schedule");
                throw new AppException(
                    ErrorCode::PERMISSION_DENIED,
                    userMessage: 'Você não tem acesso a este chat'
                );
            }

            $chat = $this->service->getById($id);
            return response()->json([
                'success' => true,
                'data' => $chat
            ]);
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to retrieve chat [{$id}]: " . $e->getMessage());
            throw new AppException(
                ErrorCode::RESOURCE_NOT_FOUND,
                userMessage: 'Chat não encontrado'
            );
        }
    }

    public function store(Request $request)
    {
        Log::info("Request to create chat");

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'chatable_id' => 'required',
                'chatable_type' => ['required', new Enum(ChatType::class)],
                'user_creator' => 'required|integer|exists:users,id',
            ]);

            $hasPermission = $this->permissionService->hasPermission($validated['user_creator'], 'create_chat');
            if (!$hasPermission) {
                Log::warning("User [{$validated['user_creator']}] tried to create chat but does not have permission");
                throw new AppException(
                    ErrorCode::PERMISSION_DENIED,
                    userMessage: 'Você não tem permissão para criar chats'
                );
            }

            $chat = $this->service->create($validated);
            return response()->json([
                'success' => true,
                'data' => $chat
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("Chat creation validation failed: " . json_encode($e->errors()));
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to create chat: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function update(Request $request, $id)
    {
        Log::info("Request to update chat", ['chat_id' => $id]);

        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'user_updater' => 'required|integer|exists:users,id',
            ]);

            // Verificar se usuário tem acesso ao chat baseado nas áreas
            if (!$this->service->userHasAccessToChat($validated['user_updater'], $id)) {
                Log::warning("User [{$validated['user_updater']}] tried to update chat [{$id}] but doesn't belong to the area/schedule");
                throw new AppException(
                    ErrorCode::PERMISSION_DENIED,
                    userMessage: 'Você não tem acesso a este chat'
                );
            }

            $hasPermission = $this->permissionService->hasPermission($validated['user_updater'], 'update_chat');
            if (!$hasPermission) {
                Log::warning("User [{$validated['user_updater']}] tried to update chat [{$id}] but does not have permission");
                throw new AppException(
                    ErrorCode::PERMISSION_DENIED,
                    userMessage: 'Você não tem permissão para editar chats'
                );
            }

            $chat = $this->service->update($id, $validated);
            return response()->json([
                'success' => true,
                'data' => $chat
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("Chat update validation failed: " . json_encode($e->errors()));
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to update chat [{$id}]: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function destroy($id, Request $request)
    {
        $userId = $request->input('user_id');

        Log::info("Request to delete chat", ['chat_id' => $id, 'user_id' => $userId]);

        try {
            // Verificar se usuário tem acesso ao chat baseado nas áreas
            if ($userId && !$this->service->userHasAccessToChat($userId, $id)) {
                Log::warning("User [{$userId}] tried to delete chat [{$id}] but doesn't belong to the area/schedule");
                throw new AppException(
                    ErrorCode::PERMISSION_DENIED,
                    userMessage: 'Você não tem acesso a este chat'
                );
            }

            $hasPermission = $this->permissionService->hasPermission($userId, 'delete_chat');
            if (!$hasPermission) {
                Log::warning("User [{$userId}] tried to delete chat [{$id}] but does not have permission");
                throw new AppException(
                    ErrorCode::PERMISSION_DENIED,
                    userMessage: 'Você não tem permissão para excluir chats'
                );
            }

            $this->service->delete($id);

            return response()->json([
                'success' => true,
                'data' => null
            ], 204);
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to delete chat [{$id}]: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function getChats(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer',
            ]);

            $userId = $validated['user_id'];

            Log::info("Request to get chats for user", ['user_id' => $userId]);

            $hasPermission = $this->permissionService->hasPermission($userId, 'read_chat');
            if (!$hasPermission) {
                Log::warning("User [{$userId}] tried to access chats but does not have permission");
                throw new AppException(
                    ErrorCode::PERMISSION_DENIED,
                    userMessage: 'Você não tem permissão para visualizar chats'
                );
            }

            $chats = $this->service->getChatsForUser($userId);
            return response()->json([
                'success' => true,
                'data' => $chats
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("Get chats validation failed: " . json_encode($e->errors()));
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to get chats: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }
    public function getChatById(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer',
                'chat_id'   => 'required|int', 
            ]);

            $userId = $validated['user_id'];
            $chat_id  = $validated['chat_id'];

            Log::info("Request to get chat for user", ['user_id' => $userId, 'chat_id' => $chat_id]);

            $hasPermission = $this->permissionService->hasPermission($userId, 'read_chat');
            if (!$hasPermission) {
                Log::warning("User [{$userId}] tried to access chats but does not have permission");
                throw new AppException(
                    ErrorCode::PERMISSION_DENIED,
                    userMessage: 'Você não tem permissão para visualizar chats'
                );
            }

            $chat = $this->service->getChatForUserById($userId, $chat_id);
            return response()->json([
                'success' => true,
                'data' => $chat
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("Get chats validation failed: " . json_encode($e->errors()));
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to get chats: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }
}