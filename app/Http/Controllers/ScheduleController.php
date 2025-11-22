<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Enums\ScheduleType;
use App\Services\Interfaces\IPermissionService;
use App\Services\Interfaces\IScheduleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Log;

class ScheduleController extends Controller
{
    private IScheduleService $scheduleService;
    private IPermissionService $permissionService;

    public function __construct(IScheduleService $scheduleService, IPermissionService $permissionService)
    {
        $this->scheduleService = $scheduleService;
        $this->permissionService = $permissionService;
    }

    public function index()
    {
        try {
            $schedules = $this->scheduleService->getAll();
            return response()->json([
                'success' => true,
                'data' => $schedules
            ]);
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
            $schedule = $this->scheduleService->getById((int) $id);
            return response()->json([
                'success' => true,
                'data' => $schedule
            ]);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::RESOURCE_NOT_FOUND,
                userMessage: 'Escala não encontrada'
            );
        }
    }

    public function store(Request $request)
    {
        Log::info('Request to create schedule');

        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string|max:255',
                'local' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'observation' => 'nullable|string|max:255',
                'type' => ['required', Rule::in(ScheduleType::values())],
                'approved' => 'sometimes|boolean',
                'user_creator' => 'required|integer|exists:users,id'
            ]);

            $hasPermission = $this->permissionService->hasPermission($data['user_creator'], 'create_scale');
            if (!$hasPermission) {
                Log::warning("User [{$data['user_creator']}] tried to create schedule, but dont have permission");
                throw new AppException(
                    ErrorCode::PERMISSION_DENIED,
                    userMessage: 'Você não tem permissão para criar escalas'
                );
            }

            $schedule = $this->scheduleService->create($data);
            return response()->json([
                'success' => true,
                'data' => $schedule
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("Schedule creation validation failed: " . json_encode($e->errors()));
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to create schedule: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string|max:255',
                'local' => 'sometimes|string|max:255',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date|after_or_equal:start_date',
                'observation' => 'nullable|string|max:255',
                'type' => ['sometimes', Rule::in(ScheduleType::values())],
                'approved' => 'sometimes|boolean',
                'user_creator' => 'sometimes|integer|exists:users,id'
            ]);

            $schedule = $this->scheduleService->update((int) $id, $data);
            return response()->json([
                'success' => true,
                'data' => $schedule
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

    public function destroy($id)
    {
        try {
            $this->scheduleService->delete((int) $id);
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

    public function generate(Request $request, $scheduleId)
    {
        try {
            $scheduleId = (int) $scheduleId;
            
            $request->validate([
                'user_id' => 'required|integer',
                'areas' => 'required|array|min:1',
                'areas.*' => 'integer|exists:area,id',
                'roles' => 'required|array|min:1',
                'roles.*.role_id' => 'required|integer|exists:role,id',
                'roles.*.area_id' => 'required|integer|exists:area,id',
                'roles.*.count' => 'required|integer|min:1',
            ]);

            $userId = $request->user_id;

            // Verificar permissão
            Log::info("Checking permission to generate schedule", [
                'user_id' => $userId,
                'schedule_id' => $scheduleId
            ]);

            if (!$this->permissionService->canCreateScale($userId)) {
                Log::warning("User attempted to generate schedule without permission", [
                    'user_id' => $userId,
                    'schedule_id' => $scheduleId
                ]);
                throw new AppException(
                    ErrorCode::PERMISSION_DENIED,
                    userMessage: 'Você não tem permissão para gerar escalas'
                );
            }

            Log::info("Permission verified, proceeding with schedule generation", [
                'user_id' => $userId,
                'schedule_id' => $scheduleId
            ]);

            // Verificar status da escala
            $schedule = $this->scheduleService->getById($scheduleId);
            if ($schedule->status === \App\Enums\ScheduleStatus::ACTIVE) {
                throw new AppException(
                    ErrorCode::VALIDATION_ERROR,
                    userMessage: 'Não é possível gerar escala automaticamente em uma escala publicada'
                );
            }
            if ($schedule->status !== \App\Enums\ScheduleStatus::DRAFT) {
                throw new AppException(
                    ErrorCode::VALIDATION_ERROR,
                    userMessage: 'Apenas escalas em status Rascunho podem ser geradas automaticamente'
                );
            }

            $result = $this->scheduleService->generateSchedule(
                $scheduleId,
                $request->areas,
                $request->roles
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'schedule_id' => $scheduleId,
                    'selected_users' => $result['users']->map(fn($u) => [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email
                    ]),
                    'statistics' => $result['statistics']
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function publish(Request $request, $scheduleId)
    {
        try {
            $scheduleId = (int) $scheduleId;
            
            $request->validate([
                'user_id' => 'required|integer',
            ]);

            $userId = $request->user_id;

            // Verificar permissão
            if (!$this->permissionService->hasPermission($userId, 'update_scale')) {
                Log::warning("User attempted to publish schedule without permission", [
                    'user_id' => $userId,
                    'schedule_id' => $scheduleId
                ]);
                throw new AppException(
                    ErrorCode::PERMISSION_DENIED,
                    userMessage: 'Você não tem permissão para publicar escalas'
                );
            }

            // Buscar a escala
            $schedule = $this->scheduleService->getById($scheduleId);

            // Verificar se já está ativa
            if ($schedule->status === \App\Enums\ScheduleStatus::ACTIVE) {
                throw new AppException(
                    ErrorCode::VALIDATION_ERROR,
                    userMessage: 'Esta escala já está publicada'
                );
            }

            // Verificar se está em rascunho
            if ($schedule->status !== \App\Enums\ScheduleStatus::DRAFT) {
                throw new AppException(
                    ErrorCode::VALIDATION_ERROR,
                    userMessage: 'Apenas escalas em status Rascunho podem ser publicadas'
                );
            }

            // Publicar a escala (mudar status para ACTIVE)
            $schedule = $this->scheduleService->publish($scheduleId);

            return response()->json([
                'success' => true,
                'data' => $schedule
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to publish schedule [{$scheduleId}]: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }
}
