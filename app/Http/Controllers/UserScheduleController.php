<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Enums\UserScheduleStatus;
use App\Services\Interfaces\IUserScheduleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserScheduleController extends Controller
{
    private IUserScheduleService $userScheduleService;

    public function __construct(IUserScheduleService $userScheduleService)
    {
        $this->userScheduleService = $userScheduleService;
    }

    public function index()
    {
        try {
            $userSchedules = $this->userScheduleService->getAll();
            return response()->json([
                'success' => true,
                'data' => $userSchedules
            ]);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function getAllSchedules()
    {
        try {
            $schedules = $this->userScheduleService->getAllSchedules();
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
            $userSchedule = $this->userScheduleService->getById($id);
            return response()->json([
                'success' => true,
                'data' => $userSchedule
            ]);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::RESOURCE_NOT_FOUND,
                userMessage: 'Agendamento de usuário não encontrado'
            );
        }
    }

    public function getUsersByScheduleId(int $id)
    {
        try {
            $users = $this->userScheduleService->getUsersByScheduleId($id);
            return response()->json([
                'success' => true,
                'data' => $users
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
                'schedule_id' => 'required|integer|exists:schedules,id',
                'user_id' => 'required|integer|exists:users,id',
                'status' => ['required', Rule::in(UserScheduleStatus::cases())]
            ]);

            $userSchedule = $this->userScheduleService->create($data);
            return response()->json([
                'success' => true,
                'data' => $userSchedule
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

    public function updateStatus(Request $request)
    {
        try {
            $data = $request->validate([
                'schedule_id' => 'sometimes|integer|exists:schedule,id',
                // 'user_id' =>  auth()->id(),
                'status' => ['required', Rule::in(UserScheduleStatus::cases())]
            ]);

            $data['user_id'] = auth()->id();
            // $data['schedule_id'] = $scheduleId;
            $data['area_id'] = 1; // Temporário, ajustar depois

            $userSchedule = $this->userScheduleService->update($data);
            return response()->json([
                'success' => true,
                'data' => $userSchedule
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

    public function update(Request $request)
    {
        try {
            $data = $request->validate([
                'schedule_id' => 'sometimes|integer|exists:schedules,id',
                'user_id' => 'sometimes|integer|exists:users,id',
                'status' => ['sometimes', Rule::in(UserScheduleStatus::cases())]
            ]);

            $userSchedule = $this->userScheduleService->update($data);
            return response()->json([
                'success' => true,
                'data' => $userSchedule
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
            $this->userScheduleService->delete($id);
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
