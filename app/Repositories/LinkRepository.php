<?php

namespace App\Repositories;

use App\Models\Link;
use Illuminate\Database\Eloquent\Collection;

class LinkRepository
{
    public function create(array $data): Link
    {
        return Link::create($data);
    }

    public function getAll(): Collection
    {
        return Link::all();
    }

    public function getById(int $id): Link
    {
        return Link::findOrFail($id);
    }

    public function update(int $id, array $data): Link
    {
        $link = Link::findOrFail($id);
        $link->update($data);
        return $link;
    }

    public function delete(int $id): bool
    {
        $link = Link::findOrFail($id);
        return $link->delete();
    }
}
