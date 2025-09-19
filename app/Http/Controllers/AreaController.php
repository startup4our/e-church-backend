<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\IAreaService;
use App\Services\Interfaces\IPermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AreaController extends Controller
{
    private IAreaService $areaService;
    private IPermissionService $permissionService;

    public function __construct(IAreaService $areaService, IPermissionService $permissionService)
    {
        $this->areaService = $areaService;
        $this->permissionService = $permissionService;
    }

    public function index()
    {
        $user = Auth::user();
        Log::info("User [{$user->id}] requested to list areas for church [{$user->church_id}]");
        
        $areas = $this->areaService->getByChurchId($user->church_id);
        Log::info("Retrieved " . $areas->count() . " areas for user [{$user->id}] from church [{$user->church_id}]");
        
        return response()->json($areas);
    }

    public function show(int $id)
    {
        $user = Auth::user();
        Log::info("User [{$user->id}] requested to view area [{$id}]");
        
        try {
            $area = $this->areaService->getByIdAndChurchId($id, $user->church_id);
            Log::info("Area [{$id}] retrieved successfully for user [{$user->id}] from church [{$user->church_id}]");
            return response()->json($area);
        } catch (\Exception $e) {
            Log::error("Failed to retrieve area [{$id}] for user [{$user->id}] from church [{$user->church_id}]: " . $e->getMessage());
            return response()->json(['message' => 'Área não encontrada'], 404);
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission to create areas
        if (!$this->permissionService->hasPermission($user->id, 'create_area')) {
            Log::warning("User [{$user->id}] attempted to create area without permission");
            return response()->json(['message' => 'Você não tem permissão para criar áreas'], 403);
        }

        Log::info("User [{$user->id}] attempting to create new area");

        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);

            $data['church_id'] = $user->church_id;
            $area = $this->areaService->create($data);
            Log::info("Area [{$area->id}] '{$area->name}' created successfully by user [{$user->id}]");
            
            return response()->json($area, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("Area creation validation failed for user [{$user->id}]: " . json_encode($e->errors()));
            return response()->json(['message' => 'Dados inválidos', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error("Failed to create area for user [{$user->id}]: " . $e->getMessage());
            return response()->json(['message' => 'Erro interno do servidor'], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        $user = Auth::user();
        
        // Check if user has permission to update areas
        if (!$this->permissionService->hasPermission($user->id, 'update_area')) {
            Log::warning("User [{$user->id}] attempted to update area [{$id}] without permission");
            return response()->json(['message' => 'Você não tem permissão para editar áreas'], 403);
        }

        Log::info("User [{$user->id}] attempting to update area [{$id}]");

        try {
            $data = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);

            $area = $this->areaService->updateByIdAndChurchId($id, $user->church_id, $data);
            Log::info("Area [{$id}] '{$area->name}' updated successfully by user [{$user->id}]");
            
            return response()->json($area);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("Area update validation failed for user [{$user->id}] on area [{$id}]: " . json_encode($e->errors()));
            return response()->json(['message' => 'Dados inválidos', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error("Failed to update area [{$id}] for user [{$user->id}]: " . $e->getMessage());
            return response()->json(['message' => 'Erro interno do servidor'], 500);
        }
    }

    public function destroy(int $id)
    {
        $user = Auth::user();
        
        // Check if user has permission to delete areas
        if (!$this->permissionService->hasPermission($user->id, 'delete_area')) {
            Log::warning("User [{$user->id}] attempted to delete area [{$id}] without permission");
            return response()->json(['message' => 'Você não tem permissão para excluir áreas'], 403);
        }

        Log::info("User [{$user->id}] attempting to delete area [{$id}]");

        try {
            $this->areaService->deleteByIdAndChurchId($id, $user->church_id);
            Log::info("Area [{$id}] deleted successfully by user [{$user->id}]");
            
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error("Failed to delete area [{$id}] for user [{$user->id}]: " . $e->getMessage());
            return response()->json(['message' => 'Erro interno do servidor'], 500);
        }
    }
}
