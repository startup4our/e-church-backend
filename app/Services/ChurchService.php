<?php

namespace App\Services;

use App\Models\Church;
use App\Repositories\ChurchRepository;
use App\Services\Interfaces\IChurchService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class ChurchService implements IChurchService
{
    public function __construct(private ChurchRepository $repository) {}

    public function create(array $data): Church
    {
        if ($this->repository->existsDuplicate($data)) {
            throw ValidationException::withMessages([
                'name' => 'Igreja já cadastrada para este endereço (name/cep/number).'
            ]);
        }
        return $this->repository->create($data);
    }

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function getById(string $id): Church
    {
        return $this->repository->getById($id);
    }

    public function update(string $id, array $data): Church
    {
        if ($this->repository->existsDuplicate($data, $id)) {
            throw ValidationException::withMessages([
                'name' => 'Já existe outra igreja com este endereço.'
            ]);
        }
        return $this->repository->update($id, $data);
    }

    public function delete(string $id): bool
    {
        return $this->repository->delete($id);
    }

    public function getChurchesForRegister(): array
    {
        return $this->repository->getChurchesForRegister();
    }
}
