<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\IRoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(private IRoleService $service) {}

    public function index()
    {
        return response()->json($this->service->getAll());
    }

    public function show(int $id)
    {
        return response()->json($this->service->getById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:120'],
            'description' => ['nullable','string','max:255'],
            'area_id'     => ['required','exists:area,id'],
        ]);

        return response()->json($this->service->create($data), 201);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'name'        => ['sometimes','string','max:120'],
            'description' => ['nullable','string','max:255'],
            'area_id'     => ['sometimes','exists:area,id'],
        ]);

        return response()->json($this->service->update($id, $data));
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);
        return response()->json(null, 204);
    }
}
