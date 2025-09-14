<?php

namespace App\Repositories;

use App\Models\Church;
use App\Models\DTO\ChurchDTO;
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
        $q = Church::query();

        if (!empty($data['name']))   $q->where('name',   $data['name']);
        if (!empty($data['cep']))    $q->where('cep',    $data['cep']);
        if (!empty($data['number'])) $q->where('number', $data['number']);

        if ($ignoreId) $q->where('id', '!=', $ignoreId);

        return $q->exists();
    }

    public function getChurchesForRegister(): array
    {
        return Church::select('id', 'name')
        ->get()
        ->map(fn ($church) => new ChurchDTO(
            id: $church->id,
            name: $church->name
        ))
        ->toArray();
    }
}
