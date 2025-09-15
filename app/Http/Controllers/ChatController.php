<?php

namespace App\Http\Controllers;

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

        return response()->json($this->service->getAll());
    }

    public function show($id)
    {
        Log::info("Request to show chat", ['chat_id' => $id]);

        return response()->json($this->service->getById($id));
    }

    public function store(Request $request)
    {
        Log::info("Request to create chat");

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

            return response()->json([
                "error" => "Unauthorized",
                "message" => "You don't have permission to create chats"
            ], 401);
        }

        return response()->json($this->service->create($validated), 201);
    }

    public function update(Request $request, $id)
    {
        Log::info("Request to update chat", ['chat_id' => $id]);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'user_updater' => 'required|integer|exists:users,id',
        ]);

        $hasPermission = $this->permissionService->hasPermission($validated['user_updater'], 'update_chat');
        if (!$hasPermission) {
            Log::warning("User [{$validated['user_updater']}] tried to update chat [{$id}] but does not have permission");

            return response()->json([
                "error" => "Unauthorized",
                "message" => "You don't have permission to update chats"
            ], 401);
        }

        return response()->json($this->service->update($id, $validated));
    }

    public function destroy($id, Request $request)
    {
        $userId = $request->input('user_id'); // quem está tentando deletar

        Log::info("Request to delete chat", ['chat_id' => $id, 'user_id' => $userId]);

        $hasPermission = $this->permissionService->hasPermission($userId, 'delete_chat');
        if (!$hasPermission) {
            Log::warning("User [{$userId}] tried to delete chat [{$id}] but does not have permission");

            return response()->json([
                "error" => "Unauthorized",
                "message" => "You don't have permission to delete chats"
            ], 401);
        }

        $this->service->delete($id);

        return response()->json(null, 204);
    }

    public function getChats(Request $request)
    {
        $userId = $request->input('user_id');
        $areas = $request->input('areas', []);

        Log::info("Request to get chats for user", ['user_id' => $userId, 'areas' => $areas]);

        $hasPermission = $this->permissionService->hasPermission($userId, 'read_chat');
        if (!$hasPermission) {
            Log::warning("User [{$userId}] tried to access chats but does not have permission");

            return response()->json([
                "error" => "Unauthorized",
                "message" => "You don't have permission to view chats"
            ], 401);
        }

        return response()->json($this->service->getChatsForUser($userId, $areas));
    }
}
