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

    public function show(int $id)
    {
        try {
            $schedule = $this->scheduleService->getById($id);
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
                'local' => 'nullable|string|max:255',
                'date_time' => 'required|date',
                'observation' => 'nullable|string|max:255',
                'type' => ['required', Rule::in(ScheduleType::values())],
                'approved' => 'boolean',
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

    public function update(Request $request, int $id)
    {
        try {
            $data = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string|max:255',
                'local' => 'nullable|string|max:255',
                'date_time' => 'sometimes|date',
                'observation' => 'nullable|string|max:255',
                'type' => ['sometimes', Rule::in(ScheduleType::values())],
                'approved' => 'boolean',
                'user_creator' => 'sometimes|integer|exists:users,id'
            ]);

            $schedule = $this->scheduleService->update($id, $data);
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

    public function destroy(int $id)
    {
        try {
            $this->scheduleService->delete($id);
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

    public function generate(Request $request, int $scheduleId)
    {
        try {
            $request->validate([
                'user_id' => 'required|integer',
                'areas' => 'required|array|min:1',
                'max_users' => 'required|integer|min:1',
            ]);

            $userId = $request->user_id;

            // Verificar permissão
            if (!$this->permissionService->canCreateScale($userId)) {
                throw new AppException(
                    ErrorCode::PERMISSION_DENIED,
                    userMessage: 'Você não tem permissão para gerar escalas'
                );
            }

            $selectedUsers = $this->scheduleService->generateSchedule(
                $scheduleId,
                $request->areas,
                $request->max_users
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'schedule_id' => $scheduleId,
                    'selected_users' => $selectedUsers->map(fn($u) => [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email
                    ])
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
}
