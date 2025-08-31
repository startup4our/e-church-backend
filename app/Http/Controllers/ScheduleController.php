<?php

namespace App\Http\Controllers;

use App\Enums\ScheduleType;
use App\Services\Interfaces\IScheduleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{
    private IScheduleService $scheduleService;

    public function __construct(IScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
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
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'local' => 'nullable|string|max:255',
            'date_time' => 'required|date',
            'observation' => 'nullable|string|max:255',
            'type' => ['required', Rule::in(ScheduleType::values())],
            'aproved' => 'boolean',
            'user_creator' => 'required|integer|exists:users,id'
        ]);

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
            'aproved' => 'boolean',
            'user_creator' => 'sometimes|integer|exists:users,id'
        ]);

        return response()->json($this->scheduleService->update($id, $data));
    }

    public function destroy(int $id)
    {
        $this->scheduleService->delete($id);
        return response()->json(null, 204);
    }
}
