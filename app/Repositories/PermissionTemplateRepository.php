<?php

namespace App\Repositories;

use App\Models\PermissionTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PermissionTemplateRepository
{
    public function create(array $data): PermissionTemplate
    {
        return PermissionTemplate::create($data);
    }

    public function paginate(?string $q = null, int $perPage = 15): LengthAwarePaginator
    {
        return PermissionTemplate::query()
            ->when($q, fn($qb) =>
                $qb->where('name','like',"%$q%")
                   ->orWhere('description','like',"%$q%")
            )
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function find(int $id): PermissionTemplate
    {
        return PermissionTemplate::findOrFail($id);
    }

    public function update(int $id, array $data): PermissionTemplate
    {
        $tpl = PermissionTemplate::findOrFail($id);
        $tpl->update($data);
        return $tpl;
    }

    public function delete(int $id): bool
    {
        $tpl = PermissionTemplate::findOrFail($id);
        return (bool) $tpl->delete();
    }
}
