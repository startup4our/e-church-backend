<?php

namespace App\Services;

use App\Models\PermissionTemplate;
use App\Repositories\PermissionTemplateRepository;
use App\Services\Interfaces\IPermissionTemplateService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PermissionTemplateService implements IPermissionTemplateService
{
    public function __construct(private PermissionTemplateRepository $repo) {}

    public function create(array $data): PermissionTemplate
    {
        return $this->repo->create($data);
    }

    public function list(?string $q = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repo->paginate($q, $perPage);
    }

    public function get(int $id): PermissionTemplate
    {
        return $this->repo->find($id);
    }

    public function update(int $id, array $data): PermissionTemplate
    {
        return $this->repo->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repo->delete($id);
    }
}
