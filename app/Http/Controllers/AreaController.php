<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\IAreaService;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    private IAreaService $areaService;

    public function __construct(IAreaService $areaService)
    {
        $this->areaService = $areaService;
    }

    public function index()
    {
        return response()->json($this->areaService->getAll());
    }

    public function show(int $id)
    {
        return response()->json($this->areaService->getById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        return response()->json($this->areaService->create($data), 201);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
        ]);

        return response()->json($this->areaService->update($id, $data));
    }

    public function destroy(int $id)
    {
        $this->areaService->delete($id);
        return response()->json(null, 204);
    }
}
