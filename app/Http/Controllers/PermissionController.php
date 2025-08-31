<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Services\Interfaces\IPermissionService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    protected IPermissionService $service;

    public function __construct(IPermissionService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return $this->service->listAll();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
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

        $permission = $this->service->create($data);
        return response()->json($permission, 201);
    }

    public function show($id)
    {
        return $this->service->get($id);
    }

    public function update(Request $request, Permission $permission)
    {
        $data = $request->validate([
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

        return $this->service->update($permission, $data);
    }

    public function destroy(Permission $permission)
    {
        $this->service->delete($permission);
        return response()->noContent();
    }
}
