<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
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
        try {
            $recordings = $this->recordingService->getAll();
            return response()->json([
                'success' => true,
                'data' => $recordings
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
            $recording = $this->recordingService->getById($id);
            return response()->json([
                'success' => true,
                'data' => $recording
            ]);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::RESOURCE_NOT_FOUND,
                userMessage: 'Gravação não encontrada'
            );
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'path' => 'required|string',
                'type' => ['required', Rule::in(RecordingType::values())],
                'description' => 'nullable|string',
                'song_id' => 'required|integer|exists:song,id'
            ]);

            $recording = $this->recordingService->create($data);
            return response()->json([
                'success' => true,
                'data' => $recording
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

    public function update(Request $request, int $id)
    {
        try {
            $data = $request->validate([
                'path' => 'sometimes|string',
                'type' => ['sometimes', Rule::in(RecordingType::values())],
                'description' => 'nullable|string',
                'song_id' => 'sometimes|integer|exists:song,id'
            ]);

            $recording = $this->recordingService->update($id, $data);
            return response()->json([
                'success' => true,
                'data' => $recording
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
            $this->recordingService->delete($id);
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
