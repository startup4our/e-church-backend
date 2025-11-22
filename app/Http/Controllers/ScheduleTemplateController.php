<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Enums\ScheduleType;
use App\Services\Interfaces\IScheduleTemplateService;
use App\Services\Interfaces\IPermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ScheduleTemplateController extends Controller
{
    private IScheduleTemplateService $templateService;
    private IPermissionService $permissionService;

    public function __construct(
        IScheduleTemplateService $templateService,
        IPermissionService $permissionService
    ) {
        $this->templateService = $templateService;
        $this->permissionService = $permissionService;
    }

    /**
     * Lista templates do usuário
     */
    public function index()
    {
        $user = Auth::user();

        try {
            // Verificar permissão de criar escala
            if (!$this->permissionService->hasPermission($user->id, 'create_scale')) {
                Log::warning("Tentativa de listar templates sem permissão", ['user_id' => $user->id]);
                throw new AppException(
                    ErrorCode::PERMISSION_DENIED,
                    userMessage: 'Você não tem permissão para criar escalas'
                );
            }

            $templates = $this->templateService->getUserTemplates($user->id);
            
            return response()->json([
                'success' => true,
                'data' => $templates
            ]);
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Erro ao listar templates", [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao listar templates'
            );
        }
    }

    /**
     * Cria template
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'type' => ['required', Rule::in(ScheduleType::values())],
                'music_template_id' => 'nullable|integer',
                'role_requirements' => 'required|array|min:1',
                'role_requirements.*.area_id' => 'required|integer|exists:area,id',
                'role_requirements.*.role_id' => 'required|integer|exists:role,id',
                'role_requirements.*.count' => 'required|integer|min:1'
            ]);

            $template = $this->templateService->create($data, $user->id);

            return response()->json([
                'success' => true,
                'data' => $template
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("Validação falhou", [
                'user_id' => $user->id,
                'errors' => $e->errors()
            ]);
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Erro ao criar template", [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao criar template'
            );
        }
    }

    /**
     * Busca template por ID
     */
    public function show(int $id)
    {
        $user = Auth::user();

        try {
            $template = $this->templateService->getTemplateById($id, $user->id);

            return response()->json([
                'success' => true,
                'data' => $template
            ]);
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Erro ao buscar template", [
                'template_id' => $id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao buscar template'
            );
        }
    }

    /**
     * Atualiza template
     */
    public function update(Request $request, int $id)
    {
        $user = Auth::user();

        try {
            $data = $request->validate([
                'name' => 'sometimes|string|max:255',
                'type' => ['sometimes', Rule::in(ScheduleType::values())],
                'music_template_id' => 'nullable|integer',
                'role_requirements' => 'sometimes|array|min:1',
                'role_requirements.*.area_id' => 'required|integer|exists:area,id',
                'role_requirements.*.role_id' => 'required|integer|exists:role,id',
                'role_requirements.*.count' => 'required|integer|min:1'
            ]);

            $template = $this->templateService->update($id, $data, $user->id);

            return response()->json([
                'success' => true,
                'data' => $template
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("Validação falhou", [
                'template_id' => $id,
                'user_id' => $user->id,
                'errors' => $e->errors()
            ]);
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Erro ao atualizar template", [
                'template_id' => $id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao atualizar template'
            );
        }
    }

    /**
     * Deleta template
     */
    public function destroy(int $id)
    {
        $user = Auth::user();

        try {
            $this->templateService->delete($id, $user->id);

            return response()->json([
                'success' => true,
                'data' => null
            ], 204);
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Erro ao deletar template", [
                'template_id' => $id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao deletar template'
            );
        }
    }
}

