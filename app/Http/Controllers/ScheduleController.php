<?php

namespace App\Http\Controllers;

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
        return response()->json($this->scheduleService->getAll());
    }

    public function show(int $id)
    {
        return response()->json($this->scheduleService->getById($id));
    }

    public function store(Request $request)
    {
        Log::info('Request to create schedule');

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
            Log::warning(`User [{$data['user_creator']}] tried to create schedule, but dont have permission`);
            return response()->json([
                "error" => "Unauthorized",
                "message" => "You don't have permission to create schedules"
            ], 401);
        }

        return response()->json($this->scheduleService->create($data), 201);
    }

    public function update(Request $request, int $id)
    {
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

        return response()->json($this->scheduleService->update($id, $data));
    }

    public function destroy(int $id)
    {
        $this->scheduleService->delete($id);
        return response()->json(null, 204);
    }

    public function generate(Request $request, int $scheduleId)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'areas' => 'required|array|min:1',
            'max_users' => 'required|integer|min:1',
        ]);

        $userId = $request->user_id;

        // Verificar permissão
        if (!$this->permissionService->canCreateScale($userId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $selectedUsers = $this->scheduleService->generateSchedule(
            $scheduleId,
            $request->areas,
            $request->max_users
        );

        return response()->json([
            'schedule_id' => $scheduleId,
            'selected_users' => $selectedUsers->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email
            ])
        ]);
    }
}
