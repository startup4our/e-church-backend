<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Services\Interfaces\ISongService;
use Illuminate\Http\Request;

class SongController extends Controller
{
    public function __construct(private ISongService $service) {}

    public function index(Request $request)
    {
        try {
            $q = $request->get('q');
            $perPage = (int) $request->get('per_page', 15);
            $songs = $this->service->list($q, $perPage);
            return response()->json([
                'success' => true,
                'data' => $songs
            ]);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function show(int $id)
    {
        try {
            $song = $this->service->get($id);
            return response()->json([
                'success' => true,
                'data' => $song
            ]);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::RESOURCE_NOT_FOUND,
                userMessage: 'Música não encontrada'
            );
        }
    }

    public function store(Request $request)
    {
        try {
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

            $song = $this->service->create($data);
            return response()->json([
                'success' => true,
                'data' => $song
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

    public function update(Request $request, int $id)
    {
        try {
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

            $song = $this->service->update($id, $data);
            return response()->json([
                'success' => true,
                'data' => $song
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

    public function destroy(int $id)
    {
        try {
            $this->service->delete($id);
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
