<?php

namespace App\Services;

use App\Models\DTO\HandoutDTO;
use App\Services\Interfaces\IHandoutService;
use App\Repositories\HandoutRepository;
use App\Models\Handout;
use App\Enums\HandoutStatus;
use Illuminate\Support\Facades\Log;

class HandoutService implements IHandoutService
{
    public function __construct(
        protected HandoutRepository $repository
    ) {}

    /**
     * Cria um novo handout
     */
    public function create(array $data): Handout
    {
        // Define status inicial
        if (isset($data['activate']) && $data['activate']) {
            $data['status'] = HandoutStatus::ACTIVE->value;
            $data['start_date'] = now();
        } else {
            $data['status'] = HandoutStatus::PENDING->value;
        }

        return $this->repository->create($data);
    }

    /**
     * Atualiza um handout existente
     */
    public function update(array $data)
    {
        $handout = Handout::findOrFail($data['id']);
        unset($data['id']);

        // Atualiza status caso haja toggle manual
        if (isset($data['status'])) {
            $validStatuses = [
                HandoutStatus::ACTIVE->value,
                HandoutStatus::PENDING->value,
                HandoutStatus::INACTIVE->value,
            ];
            if (!in_array($data['status'], $validStatuses)) {
                throw new \InvalidArgumentException('Status inválido.');
            }
        }

        return $this->repository->update($handout, $data);
    }

    /**
     * Deleta (inativa) um handout
     */
    public function delete(array $data)
    {
        $handout = Handout::findOrFail($data['id']);
        return $this->repository->delete($handout);
    }

    /**
     * Retorna todos os handouts ativos e visíveis para uma igreja
     */
    public function getActiveForChurch(int $churchId, int $userId)
    {

        $userAreas = app(AreaService::class)->getUserAreas($userId);

        $handouts = $this->repository->getVisibleNow($churchId, $userAreas->pluck('id')->toArray());

        foreach($handouts as $handout){
            if ($handout->image_url) {
                $handout->image_url = app(StorageService::class)->getSignedUrl($handout->image_url);
            }
        }

        return $handouts;
    }

    /**
     * Retorna todos os handouts de uma igreja
     */
    public function getAllForChurch(int $churchId)
    {
        return $this->repository->all($churchId);
    }

    public function getById(int $id)
    {
        return Handout::find($id);
    }
}
