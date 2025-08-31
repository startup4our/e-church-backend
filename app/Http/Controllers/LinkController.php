<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\ILinkService;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    private ILinkService $linkService;

    public function __construct(ILinkService $linkService)
    {
        $this->linkService = $linkService;
    }

    public function index()
    {
        return response()->json($this->linkService->getAll());
    }

    public function show(int $id)
    {
        return response()->json($this->linkService->getById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'destination' => 'required|url',
            'description' => 'nullable|string',
            'song_id' => 'required|exists:song,id',
        ]);

        return response()->json($this->linkService->create($data), 201);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'destination' => 'sometimes|url',
            'description' => 'nullable|string',
            'song_id' => 'sometimes|exists:song,id',
        ]);

        return response()->json($this->linkService->update($id, $data));
    }

    public function destroy(int $id)
    {
        $this->linkService->delete($id);
        return response()->json(null, 204);
    }
}
