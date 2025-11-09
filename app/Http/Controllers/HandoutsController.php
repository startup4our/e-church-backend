<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Services\Interfaces\IHandoutService;
use App\Services\Interfaces\IPermissionService;
use App\Services\Interfaces\IStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HandoutsController extends Controller
{
    private IHandoutService $handoutService;
    private IPermissionService $permissionService;
    private IStorageService $storageService;

    public function __construct(IHandoutService $handoutService, IPermissionService $permissionService, IStorageService $storageService)
    {
        $this->handoutService = $handoutService;
        $this->permissionService = $permissionService;
        $this->storageService = $storageService;
    }

    /**
     * Lista todos os handouts da igreja do usuário logado
     */
    public function index()
    {
        $user = Auth::user();

        if (!$this->permissionService->hasPermission($user->id, 'manage_handouts')) {
            throw new AppException(ErrorCode::FORBIDDEN, userMessage: "Acesso negado.");
        }

        Log::info("User [{$user->id}] listing handouts for church [{$user->church_id}]");

        $handouts = $this->handoutService->getAllForChurch($user->church_id);

        return response()->json([
            'success' => true,
            'data' => $handouts
        ]);
    }

    /**
     * Cria um novo handout
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$this->permissionService->hasPermission($user->id, 'manage_handouts')) {
            throw new AppException(ErrorCode::FORBIDDEN, userMessage: "Acesso negado.");
        }

        $validated = $request->validate([
            'church_id' => 'nullable|int',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'priority' => 'in:high,normal',
            'status' => 'nullable|string|in:A,P,I',
            'area_id' => 'nullable|int',
            'link_name' => 'nullable|string|max:255',
            'link_url' => 'nullable|string|max:255',    
            'activate' => 'nullable|',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        $validated['church_id'] = $user->church_id;
        $validated['activate'] = $request->boolean('activate');

        try {
            Log::info("User [{$user->id}] creating handout for church [{$user->church_id}]");

            // Upload image, if exists
            if ($request->hasFile('image')) {
                $file = $request->file('image');

                // categoria = 'handouts'
                $upload = $this->storageService->uploadImage($file, 'handouts');

                if (!$upload['success']) {
                    throw new \Exception("Falha ao enviar imagem");
                }

                $validated['image_url'] = $upload['data']['file_path'];
            }

            $handout = $this->handoutService->create($validated);

            return response()->json([
                'success' => true,
                'data' => $handout
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao criar handout", ['exception' => $e]);
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: "Erro ao criar handout."
            );
        }
    }


    /**
     * Atualiza um handout existente
     */
    public function update(Request $request, int $id)
    {
        $user = Auth::user();

        if (!$this->permissionService->hasPermission($user->id, 'manage_handouts')) {
            throw new AppException(ErrorCode::FORBIDDEN, userMessage: "Acesso negado.");
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'area_id' => 'nullable|int',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'priority' => 'in:high,normal',
            'status' => 'nullable|string|in:A,P,I',
            'link_name' => 'nullable|string|max:255',
            'link_url' => 'nullable|string|max:255',
            'image_url' => 'nullable|string|max:255',
        ]);

        try {
            $handout = $this->handoutService->getById($id);

            if (!$handout) {
                throw new AppException(ErrorCode::HANDOUT_NOT_FOUND, userMessage: "Handout não encontrado.");
            }

            // ✅ Verifica se pertence à mesma igreja
            if ($handout->church_id !== $user->church_id) {
                throw new AppException(ErrorCode::FORBIDDEN, userMessage: "Você não tem permissão para alterar este handout.");
            }

            $validated['id'] = $id;

            Log::info("User [{$user->id}] updating handout [{$id}] for church [{$user->church_id}]");

            $updated = $this->handoutService->update($validated);

            return response()->json([
                'success' => true,
                'data' => $updated
            ]);
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: "Erro ao atualizar handout."
            );
        }
    }

    /**
     * Inativa (deleta) um handout
     */
    public function destroy(int $id)
    {
        $user = Auth::user();

        if (!$this->permissionService->hasPermission($user->id, 'manage_handouts')) {
            throw new AppException(ErrorCode::FORBIDDEN, userMessage: "Acesso negado.");
        }

        try {
            $handout = $this->handoutService->getById($id);

            if (!$handout) {
                throw new AppException(ErrorCode::HANDOUT_NOT_FOUND, userMessage: "Handout não encontrado.");
            }

            if ($handout->church_id !== $user->church_id) {
                throw new AppException(ErrorCode::FORBIDDEN, userMessage: "Você não tem permissão para excluir este handout.");
            }

            Log::info("User [{$user->id}] deleting handout [{$id}]");

            $this->handoutService->delete(['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Handout inativado com sucesso.'
            ]);
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                message: $e->getMessage(),
                userMessage: "Erro ao inativar handout."
            );
        }
    }

    /**
     * Lista os handouts ativos/visíveis
     */
    public function active()
    {
        $user = Auth::user();

        Log::info("User [{$user->id}] listing active handouts for church [{$user->church_id}]");

        $handouts = $this->handoutService->getActiveForChurch($user->church_id, $user->id);

        return response()->json([
            'success' => true,
            'data' => $handouts
        ]);
    }
}
