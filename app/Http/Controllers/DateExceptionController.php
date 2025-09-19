<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Models\DateException;
use App\Services\Interfaces\IDateExceptionService;
use Illuminate\Http\Request;

class DateExceptionController extends Controller
{
    protected IDateExceptionService $service;

    public function __construct(IDateExceptionService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        try {
            $exceptions = $this->service->listAll();
            return response()->json([
                'success' => true,
                'data' => $exceptions
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
                'exception_date' => 'required|date',
                'shift' => 'required|in:morning,afternoon,night',
                'justification' => 'nullable|string',
                'user_id' => 'required'
            ]);

            $exception = $this->service->create($data);
            return response()->json([
                'success' => true,
                'data' => $exception
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

    public function show($id)
    {
        try {
            $exception = $this->service->get($id);
            return response()->json([
                'success' => true,
                'data' => $exception
            ]);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::RESOURCE_NOT_FOUND,
                userMessage: 'Exceção de data não encontrada'
            );
        }
    }

    public function update(Request $request, DateException $exception)
    {
        try {
            $data = $request->validate([
                'exception_date' => 'required|date',
                'shift' => 'required|in:morning,afternoon,night',
                'justification' => 'nullable|string',
            ]);

            $updatedException = $this->service->update($exception, $data);
            return response()->json([
                'success' => true,
                'data' => $updatedException
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

    public function destroy(DateException $exception)
    {
        try {
            $this->service->delete($exception);
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
