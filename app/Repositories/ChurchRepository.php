<?php

namespace App\Repositories;

use App\Models\Church;
use Illuminate\Database\Eloquent\Collection;

class ChurchRepository
{
    public function create(array $data): Church
    {
        return Church::create($data);
    }

    public function getAll(): Collection
    {
        return Church::orderBy('name')->get();
    }

    public function getById(string $id): Church
    {
        return Church::findOrFail($id);
    }

    public function update(string $id, array $data): Church
    {
        $church = Church::findOrFail($id);
        $church->update($data);
        return $church;
    }

    public function delete(string $id): bool
    {
        $church = Church::findOrFail($id);
        return (bool) $church->delete();
    }

    public function existsDuplicate(array $data, ?string $ignoreId = null): bool
    {
        $q = \App\Models\Church::query();

        if (!empty($data['name']))   $q->where('name',   $data['name']);
        if (!empty($data['cep']))    $q->where('cep',    $data['cep']);
        if (!empty($data['number'])) $q->where('number', $data['number']);

        if ($ignoreId) $q->where('id', '!=', $ignoreId);

        return $q->exists();
    }
}
