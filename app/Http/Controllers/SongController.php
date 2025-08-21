<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\ISongService;
use Illuminate\Http\Request;

class SongController extends Controller
{
    public function __construct(private ISongService $service) {}

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
        $urlRule = ['nullable','url','max:255'];
        $data = $request->validate([
            'cover_path'  => ['required','string','max:255'], // se for um upload, depois preciso trocar para file
            'name'        => ['required','string','max:160'],
            'artist'      => ['required','string','max:160'],
            'spotify_id'  => ['nullable','string','max:120'],
            'preview_url' => $urlRule,
            'duration'    => ['required','integer','min:0'],
            'album'       => ['nullable','string','max:160'],
            'spotify_url' => $urlRule,
        ]);

        return response()->json($this->service->create($data), 201);
    }

    public function update(Request $request, int $id)
    {
        $urlRule = ['nullable','url','max:255'];
        $data = $request->validate([
            'cover_path'  => ['sometimes','string','max:255'],
            'name'        => ['sometimes','string','max:160'],
            'artist'      => ['sometimes','string','max:160'],
            'spotify_id'  => ['sometimes','nullable','string','max:120'],
            'preview_url' => $urlRule,
            'duration'    => ['sometimes','integer','min:0'],
            'album'       => ['sometimes','string','max:160'],
            'spotify_url' => $urlRule,
        ]);

        return response()->json($this->service->update($id, $data));
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);
        return response()->json(null, 204);
    }
}
