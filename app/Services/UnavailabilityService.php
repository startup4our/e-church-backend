<?php

namespace App\Services;

use App\Models\Unavailability;
use App\Repositories\UnavailabilityRepository;
use App\Services\Interfaces\IUnavailabilityService;
use Illuminate\Validation\ValidationException;

class UnavailabilityService implements IUnavailabilityService
{
    protected $repository;

    public function __construct(UnavailabilityRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listAll()
    {
        return $this->repository->all();
    }

    public function create(array $data): Unavailability
    {
        if ($this->repository->exists($data)) {
            throw ValidationException::withMessages([
                'message' => 'Usuário já escalado nesse dia/turno',
            ]);
        }
        return $this->repository->create($data);
    }

    public function get(int $id): ?Unavailability
    {
        return $this->repository->findById($id);
    }

    public function update(Unavailability $unavailability, array $data): Unavailability
    {
        if ($this->repository->exists(
            ['user_id' => $unavailability->user_id, 'weekday' => $data['weekday'], 'shift' => $data['shift']],
            $unavailability->id
        )) {
            throw ValidationException::withMessages([
                'message' => 'Usuário já escalado nesse dia/turno',
            ]);
        }

        return $this->repository->update($unavailability, $data);
    }

    public function delete(Unavailability $unavailability): void
    {
        $this->repository->delete($unavailability);
    }
}
