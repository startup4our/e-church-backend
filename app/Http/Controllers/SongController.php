<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Services\Interfaces\ISongService;
use App\Services\Interfaces\IPermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SongController extends Controller
{
    public function __construct(
        private ISongService $service,
        private IPermissionService $permissionService
    ) {}

    public function index(Request $request)
    {
        try {
            Log::info('SongController@index: Starting to list songs', [
                'user_id' => $request->user()->id,
                'query' => $request->get('q'),
                'per_page' => $request->get('per_page', 15)
            ]);

            // Check if user has read_music permission
            $userId = $request->user()->id;
            if (!$this->permissionService->hasPermission($userId, 'read_music')) {
                Log::warning('SongController@index: User does not have read_music permission', [
                    'user_id' => $userId
                ]);
                throw new AppException(
                    ErrorCode::FORBIDDEN,
                    userMessage: 'Você não tem permissão para visualizar músicas'
                );
            }

            $q = $request->get('q');
            $perPage = (int) $request->get('per_page', 15);
            $songs = $this->service->list($q, $perPage);
            
            Log::info('SongController@index: Successfully retrieved songs', [
                'user_id' => $userId,
                'songs_count' => $songs->total(),
                'query' => $q
            ]);

            return response()->json([
                'success' => true,
                'data' => $songs
            ]);
        } catch (AppException $e) {
            Log::error('SongController@index: AppException occurred', [
                'user_id' => $request->user()->id ?? null,
                'error_code' => $e->getErrorCode(),
                'message' => $e->getMessage()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('SongController@index: Unexpected error occurred', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function show(int $id)
    {
        try {
            Log::info('SongController@show: Starting to retrieve song', [
                'song_id' => $id
            ]);

            $song = $this->service->get($id);
            
            Log::info('SongController@show: Successfully retrieved song', [
                'song_id' => $id,
                'song_name' => $song->name ?? 'Unknown'
            ]);

            return response()->json([
                'success' => true,
                'data' => $song
            ]);
        } catch (AppException $e) {
            Log::error('SongController@show: AppException occurred', [
                'song_id' => $id,
                'error_code' => $e->getErrorCode(),
                'message' => $e->getMessage()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('SongController@show: Unexpected error occurred', [
                'song_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new AppException(
                ErrorCode::RESOURCE_NOT_FOUND,
                userMessage: 'Música não encontrada'
            );
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('SongController@store: Starting to create new song', [
                'user_id' => $request->user()->id,
                'request_data' => $request->except(['cover_path']) // Exclude sensitive data
            ]);

            // Check if user has create_music permission
            $userId = $request->user()->id;
            if (!$this->permissionService->hasPermission($userId, 'create_music')) {
                Log::warning('SongController@store: User does not have create_music permission', [
                    'user_id' => $userId
                ]);
                throw new AppException(
                    ErrorCode::FORBIDDEN,
                    userMessage: 'Você não tem permissão para criar músicas'
                );
            }

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
            
            Log::info('SongController@store: Successfully created song', [
                'user_id' => $userId,
                'song_id' => $song->id,
                'song_name' => $song->name,
                'artist' => $song->artist
            ]);

            return response()->json([
                'success' => true,
                'data' => $song
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('SongController@store: Validation error occurred', [
                'user_id' => $request->user()->id ?? null,
                'validation_errors' => $e->errors()
            ]);
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (AppException $e) {
            Log::error('SongController@store: AppException occurred', [
                'user_id' => $request->user()->id ?? null,
                'error_code' => $e->getErrorCode(),
                'message' => $e->getMessage()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('SongController@store: Unexpected error occurred', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            Log::info('SongController@update: Starting to update song', [
                'user_id' => $request->user()->id,
                'song_id' => $id,
                'request_data' => $request->except(['cover_path']) // Exclude sensitive data
            ]);

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
            
            Log::info('SongController@update: Successfully updated song', [
                'user_id' => $request->user()->id,
                'song_id' => $id,
                'song_name' => $song->name,
                'artist' => $song->artist
            ]);

            return response()->json([
                'success' => true,
                'data' => $song
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('SongController@update: Validation error occurred', [
                'user_id' => $request->user()->id ?? null,
                'song_id' => $id,
                'validation_errors' => $e->errors()
            ]);
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (AppException $e) {
            Log::error('SongController@update: AppException occurred', [
                'user_id' => $request->user()->id ?? null,
                'song_id' => $id,
                'error_code' => $e->getErrorCode(),
                'message' => $e->getMessage()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('SongController@update: Unexpected error occurred', [
                'user_id' => $request->user()->id ?? null,
                'song_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function destroy(int $id)
    {
        try {
            Log::info('SongController@destroy: Starting to delete song', [
                'song_id' => $id
            ]);

            $this->service->delete($id);
            
            Log::info('SongController@destroy: Successfully deleted song', [
                'song_id' => $id
            ]);

            return response()->json([
                'success' => true,
                'data' => null
            ], 204);
        } catch (AppException $e) {
            Log::error('SongController@destroy: AppException occurred', [
                'song_id' => $id,
                'error_code' => $e->getErrorCode(),
                'message' => $e->getMessage()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('SongController@destroy: Unexpected error occurred', [
                'song_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }
}
