<?php

namespace App\Services;

use App\Models\Song;
use App\Repositories\SongRepository;
use App\Services\Interfaces\ISongService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class SongService implements ISongService
{
    public function __construct(private SongRepository $repo) {}

    public function create(array $data): Song
    {
        if (!empty($data['spotify_id']) && $this->repo->existsSpotifyId($data['spotify_id'])) {
            throw ValidationException::withMessages([
                'spotify_id' => 'Já existe música com esse spotify_id.'
            ]);
        }
        return $this->repo->create($data);
    }

    public function list(?string $q = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repo->paginate($q, $perPage);
    }

    public function get(int $id): Song
    {
        return $this->repo->find($id);
    }

    public function update(int $id, array $data): Song
    {
        if (!empty($data['spotify_id']) && $this->repo->existsSpotifyId($data['spotify_id'], $id)) {
            throw ValidationException::withMessages([
                'spotify_id' => 'Já existe outra música com esse spotify_id.'
            ]);
        }
        return $this->repo->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repo->delete($id);
    }
}
