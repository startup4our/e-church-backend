<?php

namespace App\Http\Controllers;

use App\Enums\ChatType;
use App\Services\Interfaces\IChatService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ChatController extends Controller
{
    protected $service;

    public function __construct(IChatService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json($this->service->getAll());
    }

    public function show($id)
    {
        return response()->json($this->service->getById($id));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'chatable_id' => 'required',
            'chatable_type' => ['required', new Enum(ChatType::class)],
        ]);

        return response()->json($this->service->create($validated), 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
        ]);

        return response()->json($this->service->update($id, $validated));
    }

    public function destroy($id)
    {
        $this->service->delete($id);
        return response()->json(null, 204);
    }
}
