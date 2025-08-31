<?php

namespace App\Http\Controllers;

use App\Enums\RecordingType;
use App\Services\Interfaces\IRecordingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RecordingController extends Controller
{
    private IRecordingService $recordingService;

    public function __construct(IRecordingService $recordingService)
    {
        $this->recordingService = $recordingService;
    }

    public function index()
    {
        return response()->json($this->recordingService->getAll());
    }

    public function show(int $id)
    {
        return response()->json($this->recordingService->getById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'path' => 'required|string',
            'type' => ['required', Rule::in(RecordingType::values())],
            'description' => 'nullable|string',
            'song_id' => 'required|integer|exists:song,id'
        ]);

        return response()->json($this->recordingService->create($data), 201);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'path' => 'sometimes|string',
            'type' => ['sometimes', Rule::in(RecordingType::values())],
            'description' => 'nullable|string',
            'song_id' => 'sometimes|integer|exists:song,id'
        ]);

        return response()->json($this->recordingService->update($id, $data));
    }

    public function destroy(int $id)
    {
        $this->recordingService->delete($id);
        return response()->json(null, 204);
    }
}
