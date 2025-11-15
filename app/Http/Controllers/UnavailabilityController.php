<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Http\Controllers\Controller;
use App\Models\Unavailability;
use App\Services\Interfaces\IUnavailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnavailabilityController extends Controller
{
    protected $service;

    public function __construct(IUnavailabilityService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        try {
            $unavailabilities = $this->service->listAll();
            return response()->json([
                'success' => true,
                'data' => $unavailabilities
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
                'user_id' => 'required|exists:users,id',
                'weekday' => 'required|in:0,1,2,3,4,5,6',
                'shift'   => 'required|in:manha,tarde,noite',
            ]);

            $unavailability = $this->service->create($data);
            return response()->json([
                'success' => true,
                'data' => $unavailability
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
            $unavailability = $this->service->get($id);
            return response()->json([
                'success' => true,
                'data' => $unavailability
            ]);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::RESOURCE_NOT_FOUND,
                userMessage: 'Indisponibilidade não encontrada'
            );
        }
    }

    public function update(Request $request, Unavailability $unavailability)
    {
        try {
            $data = $request->validate([
                'weekday' => 'required|in:0,1,2,3,4,5,6',
                'shift'   => 'required|in:manha,tarde,noite',
            ]);

            $updatedUnavailability = $this->service->update($unavailability, $data);
            return response()->json([
                'success' => true,
                'data' => $updatedUnavailability
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

    public function destroy(Unavailability $unavailability)
    {
        try {
            $this->service->delete($unavailability);
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

    public function my()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                throw new AppException(
                    ErrorCode::UNAUTHORIZED,
                    userMessage: 'Usuário não autenticado'
                );
            }

            $unavailabilities = $this->service->getByUserId($user->id);
            return response()->json([
                'success' => true,
                'data' => $unavailabilities
            ]);
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function sync(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                throw new AppException(
                    ErrorCode::UNAUTHORIZED,
                    userMessage: 'Usuário não autenticado'
                );
            }

            $data = $request->validate([
                'unavailabilities' => 'required|array',
                'unavailabilities.*.weekday' => 'required|in:0,1,2,3,4,5,6',
                'unavailabilities.*.shift' => 'required|in:manha,tarde,noite',
            ]);

            $this->service->syncByUserId($user->id, $data['unavailabilities']);

            // Retorna as indisponibilidades atualizadas
            $unavailabilities = $this->service->getByUserId($user->id);
            
            return response()->json([
                'success' => true,
                'data' => $unavailabilities,
                'message' => 'Disponibilidade atualizada com sucesso'
            ], 200);
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
