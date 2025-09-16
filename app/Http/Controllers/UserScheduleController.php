<?php

namespace App\Http\Controllers;

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
        return response()->json($this->userScheduleService->getAll());
    }

    public function getAllSchedules()
    {

        return response()->json($this->userScheduleService->getAllSchedules());
    }

    public function show(int $id)
    {
        return response()->json($this->userScheduleService->getById($id));
    }

    public function getUsersByScheduleId(int $id)
    {
        return response()->json($this->userScheduleService->getUsersByScheduleId($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'schedule_id' => 'required|integer|exists:schedules,id',
            'user_id' => 'required|integer|exists:users,id',
            'status' => ['required', Rule::in(UserScheduleStatus::cases())]
        ]);

        return response()->json($this->userScheduleService->create($data), 201);
    }

    public function updateStatus(Request $request)
    {

        $data = $request->validate([
            'schedule_id' => 'sometimes|integer|exists:schedule,id',
            // 'user_id' =>  auth()->id(),
            'status' => ['required', Rule::in(UserScheduleStatus::cases())]
        ]);

        $data['user_id'] = auth()->id();
        // $data['schedule_id'] = $scheduleId;
        $data['area_id'] = 1; // Temporário, ajustar depois


        // return response()->json($data);
        return response()->json($this->userScheduleService->update($data));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'schedule_id' => 'sometimes|integer|exists:schedules,id',
            'user_id' => 'sometimes|integer|exists:users,id',
            'status' => ['sometimes', Rule::in(UserScheduleStatus::cases())]
        ]);

        return response()->json($this->userScheduleService->update($data));
    }

    public function destroy(int $id)
    {
        $this->userScheduleService->delete($id);
        return response()->json(['message' => 'UserSchedule deleted successfully']);
    }
}
