<?php

namespace App\Services;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Models\ScheduleTemplate;
use App\Models\ScheduleTemplateRole;
use App\Repositories\ScheduleTemplateRepository;
use App\Services\Interfaces\IAreaService;
use App\Services\Interfaces\IScheduleTemplateService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ScheduleTemplateService implements IScheduleTemplateService
{
    public function __construct(
        private ScheduleTemplateRepository $repository,
        private IAreaService $areaService
    ) {}

    /**
     * Lista templates do usuário filtrados por áreas que ele tem acesso
     */
    public function getUserTemplates(int $userId): Collection
    {
        try {
            // Buscar áreas do usuário
            $userAreas = $this->areaService->getUserAreas($userId);
            $userAreaIds = $userAreas->pluck('id')->toArray();
            
            // Se usuário não tem áreas, retornar collection vazia
            if (empty($userAreaIds)) {
                Log::info("Usuário não tem áreas, retornando templates vazios", ['user_id' => $userId]);
                return collect([]);
            }
            
            // Buscar templates do usuário que pertencem a áreas que ele tem acesso
            return $this->repository->getByUserIdAndAreas($userId, $userAreaIds);
        } catch (\Exception $e) {
            Log::error("Erro ao buscar templates do usuário", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Busca template por ID (valida user_id)
     */
    public function getTemplateById(int $id, int $userId): ScheduleTemplate
    {
        try {
            return $this->repository->getByIdAndUserId($id, $userId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning("Template não encontrado", [
                'template_id' => $id,
                'user_id' => $userId
            ]);
            throw new AppException(
                ErrorCode::RESOURCE_NOT_FOUND,
                userMessage: 'Template não encontrado'
            );
        } catch (\Exception $e) {
            Log::error("Erro ao buscar template", [
                'template_id' => $id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Cria template
     */
    public function create(array $data, int $userId): ScheduleTemplate
    {
        Log::info("Criando template", [
            'user_id' => $userId,
            'name' => $data['name'] ?? null,
            'type' => $data['type'] ?? null
        ]);
        
        try {
            if (isset($data['role_requirements'])) {
                $areaIds = array_unique(array_column($data['role_requirements'], 'area_id'));
                
                // Validar que template tem apenas uma área
                if (count($areaIds) > 1) {
                    Log::warning("Tentativa de criar template com múltiplas áreas", [
                        'user_id' => $userId,
                        'areas' => $areaIds
                    ]);
                    throw new AppException(
                        ErrorCode::VALIDATION_ERROR,
                        userMessage: 'Um template pode ter apenas uma área'
                    );
                }
                
                $this->validateUserAreas($areaIds, $userId);
            }
            
            $template = $this->repository->create([
                'name' => $data['name'],
                'type' => $data['type'],
                'user_id' => $userId,
                'music_template_id' => $data['music_template_id'] ?? null
            ]);
            
            if (isset($data['role_requirements'])) {
                foreach ($data['role_requirements'] as $index => $req) {
                    ScheduleTemplateRole::create([
                        'template_id' => $template->id,
                        'area_id' => $req['area_id'],
                        'role_id' => $req['role_id'],
                        'count' => $req['count'],
                        'order' => $index
                    ]);
                }
            }
            
            $template->load('templateRoles.area', 'templateRoles.role');
            
            Log::info("Template criado", ['template_id' => $template->id]);
            
            return $template;
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Erro ao criar template", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Atualiza template
     */
    public function update(int $id, array $data, int $userId): ScheduleTemplate
    {
        Log::info("Atualizando template", ['template_id' => $id, 'user_id' => $userId]);
        
        try {
            $template = $this->repository->getByIdAndUserId($id, $userId);
            
            if (isset($data['role_requirements'])) {
                $areaIds = array_unique(array_column($data['role_requirements'], 'area_id'));
                
                // Validar que template tem apenas uma área
                if (count($areaIds) > 1) {
                    Log::warning("Tentativa de atualizar template com múltiplas áreas", [
                        'template_id' => $id,
                        'user_id' => $userId,
                        'areas' => $areaIds
                    ]);
                    throw new AppException(
                        ErrorCode::VALIDATION_ERROR,
                        userMessage: 'Um template pode ter apenas uma área'
                    );
                }
                
                $this->validateUserAreas($areaIds, $userId);
            }
            
            $template = $this->repository->update($id, $userId, [
                'name' => $data['name'] ?? $template->name,
                'type' => $data['type'] ?? $template->type,
                'music_template_id' => $data['music_template_id'] ?? $template->music_template_id
            ]);
            
            if (isset($data['role_requirements'])) {
                ScheduleTemplateRole::where('template_id', $id)->delete();
                
                foreach ($data['role_requirements'] as $index => $req) {
                    ScheduleTemplateRole::create([
                        'template_id' => $id,
                        'area_id' => $req['area_id'],
                        'role_id' => $req['role_id'],
                        'count' => $req['count'],
                        'order' => $index
                    ]);
                }
            }
            
            $template->load('templateRoles.area', 'templateRoles.role');
            
            Log::info("Template atualizado", ['template_id' => $id]);
            
            return $template;
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Erro ao atualizar template", [
                'template_id' => $id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Deleta template
     */
    public function delete(int $id, int $userId): bool
    {
        Log::info("Deletando template", ['template_id' => $id, 'user_id' => $userId]);
        
        try {
            $this->repository->getByIdAndUserId($id, $userId);
            $deleted = $this->repository->delete($id, $userId);
            
            Log::info("Template deletado", ['template_id' => $id]);
            
            return $deleted;
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Erro ao deletar template", [
                'template_id' => $id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Valida que áreas pertencem ao usuário
     */
    private function validateUserAreas(array $areaIds, int $userId): void
    {
        $userAreas = $this->areaService->getUserAreas($userId);
        $userAreaIds = $userAreas->pluck('id')->toArray();
        
        $invalidAreas = array_diff($areaIds, $userAreaIds);
        
        if (!empty($invalidAreas)) {
            Log::warning("Áreas inválidas para usuário", [
                'user_id' => $userId,
                'invalid_areas' => $invalidAreas
            ]);
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                userMessage: 'Você não tem acesso a uma ou mais áreas selecionadas'
            );
        }
    }
}

