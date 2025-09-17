<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\IPermissionTemplateService;
use Illuminate\Http\Request;

class PermissionTemplateController extends Controller
{
    public function __construct(private IPermissionTemplateService $service) {}

    public function index(Request $request)
    {
        $q = $request->get('q');
        $perPage = (int) $request->get('per_page', 15);
        return response()->json($this->service->list($q, $perPage));
    }

    public function show(int $id)
    {
        return response()->json($this->service->get($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'area_id'     => ['exists:area,id'],
            'create_scale' => 'boolean',
            'read_scale' => 'boolean',
            'update_scale' => 'boolean',
            'delete_scale' => 'boolean',
            'create_music' => 'boolean',
            'read_music' => 'boolean',
            'update_music' => 'boolean',
            'delete_music' => 'boolean',
            'create_role' => 'boolean',
            'read_role' => 'boolean',
            'update_role' => 'boolean',
            'delete_role' => 'boolean',
            'create_area' => 'boolean',
            'read_area' => 'boolean',
            'update_area' => 'boolean',
            'delete_area' => 'boolean',
            'manage_users' => 'boolean',
            'manage_church_settings' => 'boolean',
            'manage_app_settings' => 'boolean',
        ]);

        $data['created_by'] = $request->user()->id;

        return response()->json($this->service->create($data), 201);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->all();
        return response()->json($this->service->update($id, $data));
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);
        return response()->json(null, 204);
    }
}
