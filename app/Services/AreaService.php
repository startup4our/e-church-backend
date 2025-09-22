<?php

namespace App\Services;

use App\Models\Area;
use App\Models\UserArea;
use App\Repositories\AreaRepository;
use App\Repositories\ChatRepository;
use App\Services\Interfaces\IAreaService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AreaService implements IAreaService
{
    private AreaRepository $repository;
    private ChatRepository $chatRepository;

    public function __construct(AreaRepository $repository, ChatRepository $chatRepository)
    {
        $this->repository = $repository;
        $this->chatRepository = $chatRepository;
    }

    public function create(array $data): Area
    {
        Log::info("Creating new area with data: " . json_encode($data));
        
        try {
            return DB::transaction(function () use ($data) {
                
                // Criar a área
                $area = $this->repository->create($data);
                Log::info("Area [{$area->id}] '{$area->name}' created successfully");
                
                // Criar o chat padrão para a área
                $chatData = [
                    'name' => 'Chat Geral - ' . $area->name,
                    'description' => 'Chat geral da área ' . $area->name,
                    'chatable_id' => $area->id,
                    'chatable_type' => ChatType::AREA->value,
                ];

                $chat = $this->chatRepository->create($chatData);
                Log::info("Default chat created successfully for area [{$area->id}] with chat ID: {$chat->id}");

                return $area;
            });
        } catch (\Exception $e) {
            Log::error("Failed to create area: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAll(): Collection
    {
        Log::info("Retrieving all areas");
        
        try {
            $areas = $this->repository->getAll();
            Log::info("Retrieved " . $areas->count() . " areas");
            return $areas;
        } catch (\Exception $e) {
            Log::error("Failed to retrieve areas: " . $e->getMessage());
            throw $e;
        }
    }

    public function getByChurchId(int $churchId): Collection
    {
        Log::info("Retrieving areas for church [{$churchId}]");
        
        try {
            $areas = $this->repository->getByChurchId($churchId);
            Log::info("Retrieved " . $areas->count() . " areas for church [{$churchId}]");
            return $areas;
        } catch (\Exception $e) {
            Log::error("Failed to retrieve areas for church [{$churchId}]: " . $e->getMessage());
            throw $e;
        }
    }

    public function getById(int $id): Area
    {
        Log::info("Retrieving area with ID: {$id}");
        
        try {
            $area = $this->repository->getById($id);
            Log::info("Area [{$id}] retrieved successfully");
            return $area;
        } catch (\Exception $e) {
            Log::error("Failed to retrieve area [{$id}]: " . $e->getMessage());
            throw $e;
        }
    }

    public function getByIdAndChurchId(int $id, int $churchId): Area
    {
        Log::info("Retrieving area [{$id}] for church [{$churchId}]");
        
        try {
            $area = $this->repository->getByIdAndChurchId($id, $churchId);
            Log::info("Area [{$id}] retrieved successfully for church [{$churchId}]");
            return $area;
        } catch (\Exception $e) {
            Log::error("Failed to retrieve area [{$id}] for church [{$churchId}]: " . $e->getMessage());
            throw $e;
        }
    }

    public function update(int $id, array $data): Area
    {
        Log::info("Updating area [{$id}] with data: " . json_encode($data));
        
        try {
            $area = $this->repository->update($id, $data);
            Log::info("Area [{$id}] updated successfully");
            return $area;
        } catch (\Exception $e) {
            Log::error("Failed to update area [{$id}]: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateByIdAndChurchId(int $id, int $churchId, array $data): Area
    {
        Log::info("Updating area [{$id}] for church [{$churchId}] with data: " . json_encode($data));
        
        try {
            $area = $this->repository->updateByIdAndChurchId($id, $churchId, $data);
            Log::info("Area [{$id}] updated successfully for church [{$churchId}]");
            return $area;
        } catch (\Exception $e) {
            Log::error("Failed to update area [{$id}] for church [{$churchId}]: " . $e->getMessage());
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        Log::info("Deleting area with ID: {$id}");
        
        try {
            $result = $this->repository->delete($id);
            Log::info("Area [{$id}] deleted successfully");
            return $result;
        } catch (\Exception $e) {
            Log::error("Failed to delete area [{$id}]: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteByIdAndChurchId(int $id, int $churchId): bool
    {
        Log::info("Deleting area [{$id}] for church [{$churchId}]");
        
        try {
            // Check if area has associated users
            $userCount = UserArea::where('area_id', $id)->count();
            if ($userCount > 0) {
                Log::warning("Cannot delete area [{$id}] - it has {$userCount} associated users");
                throw new \App\Exceptions\AppException(
                    \App\Enums\ErrorCode::AREA_HAS_USERS,
                    userMessage: "Não é possível excluir uma área que possui usuários associados. Remova os usuários da área primeiro."
                );
            }
            
            $result = $this->repository->deleteByIdAndChurchId($id, $churchId);
            Log::info("Area [{$id}] deleted successfully for church [{$churchId}]");
            return $result;
        } catch (\App\Exceptions\AppException $e) {
            // Re-throw AppException as-is
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to delete area [{$id}] for church [{$churchId}]: " . $e->getMessage());
            throw $e;
        }
    }

    public function getUserAreas(int $user_id): Collection|Area
    {
        Log::info("Retrieving areas for user [{$user_id}]");
        $areas = $this->repository->getUserArea($user_id);
        Log::info("Retrieved " . $areas->count() . " areas for user [{$user_id}]");
        return $areas;
    }

}