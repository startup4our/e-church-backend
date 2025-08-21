<?php

namespace App\Repositories;

use App\Models\Song;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SongRepository
{
    public function create(array $data): Song
    {
        return Song::create($data);
    }

    public function paginate(?string $q = null, int $perPage = 15): LengthAwarePaginator
    {
        return Song::query()
            ->when($q, fn($qb) =>
                $qb->where('name','like',"%$q%")
                   ->orWhere('artist','like',"%$q%")
                   ->orWhere('album','like',"%$q%")
            )
            ->orderBy('artist')->orderBy('name')
            ->paginate($perPage);
    }

    public function find(int $id): Song
    {
        return Song::findOrFail($id);
    }

    public function update(int $id, array $data): Song
    {
        $song = Song::findOrFail($id);
        $song->update($data);
        return $song;
    }

    public function delete(int $id): bool
    {
        $song = Song::findOrFail($id);
        return (bool) $song->delete();
    }

    public function existsSpotifyId(string $spotifyId, ?int $ignoreId = null): bool
    {
        $q = Song::where('spotify_id', $spotifyId);
        if ($ignoreId) $q->where('id','!=',$ignoreId);
        return $q->exists();
    }
}
