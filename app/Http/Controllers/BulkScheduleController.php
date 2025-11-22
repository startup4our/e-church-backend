<?php

namespace App\Http\Controllers;

use App\Enums\RecurrenceType;
use App\Enums\ScheduleType;
use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Models\DTO\BulkScheduleCreateDTO;
use App\Services\AreaService;
use App\Services\Interfaces\IBulkScheduleService;
use App\Services\Interfaces\IPermissionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class BulkScheduleController extends Controller
{
    public function __construct(
        private IBulkScheduleService $bulkScheduleService,
        private IPermissionService $permissionService,
        private AreaService $areaService
    ) {}

    /**
     * Cria múltiplas escalas em lote
     */
    public function createBulk(Request $request)
    {
        $user = Auth::user();
        Log::info("Criando escalas em massa", ['user_id' => $user->id]);

        try {
            if (!$this->permissionService->hasPermission($user->id, 'create_scale')) {
                Log::warning("Tentativa de criar escalas sem permissão", ['user_id' => $user->id]);
                throw new AppException(
                    ErrorCode::PERMISSION_DENIED,
                    userMessage: 'Você não tem permissão para criar escalas'
                );
            }

            $data = $request->validate([
                'template_id' => 'nullable|integer|exists:schedule_template,id',
                'quantity' => 'required|integer|min:1|max:365',
                'name_base' => 'required|string|max:255',
                'description' => 'required|string|max:500',
                'local' => 'required|string|max:255',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'recurrence' => 'required|in:' . implode(',', RecurrenceType::values()),
                'auto_fill' => 'required|boolean',
                'start_date' => 'required|date|after_or_equal:today',
                'type' => ['required_without:template_id', Rule::in(ScheduleType::values())],
                'areas' => 'required_without:template_id|array|min:1',
                'areas.*' => 'integer|exists:area,id',
                'role_requirements' => 'required_without:template_id|array|min:1',
                'role_requirements.*.area_id' => 'required|integer|exists:area,id',
                'role_requirements.*.role_id' => 'required|integer|exists:role,id',
                'role_requirements.*.count' => 'required|integer|min:1',
            ]);

            if (!isset($data['template_id'])) {
                $this->validateUserAreas($data['areas'], $user->id);
            }

            $dto = $this->buildDTO($data);

            $batch = $this->bulkScheduleService->createBulkSchedules(
                $dto,
                $user->id,
                $user->church_id
            );

            Log::info("Batch criado", ['batch_id' => $batch->id, 'user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'data' => [
                    'batch_id' => $batch->id,
                    'status' => $batch->status->value,
                    'message' => 'Criação de escalas em massa iniciada. Processamento em andamento.'
                ]
            ], 202);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("Validação falhou: " . json_encode($e->errors()));
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Erro ao criar escalas em massa: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao criar escalas em massa'
            );
        }
    }

    /**
     * Retorna status do batch
     */
    public function getBatchStatus(int $batchId)
    {
        $user = Auth::user();

        try {
            $batch = $this->bulkScheduleService->getBatchStatus($batchId, $user->id);

            return response()->json([
                'success' => true,
                'data' => $batch
            ]);
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Erro ao buscar status do batch", [
                'batch_id' => $batchId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao buscar status do lote'
            );
        }
    }

    /**
     * Lista batches do usuário
     */
    public function getUserBatches()
    {
        $user = Auth::user();

        try {
            $batches = $this->bulkScheduleService->getUserBatches($user->id);

            return response()->json([
                'success' => true,
                'data' => $batches
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao listar batches", [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao listar lotes'
            );
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

    /**
     * Constrói DTO a partir dos dados do request
     */
    private function buildDTO(array $data): BulkScheduleCreateDTO
    {
        if (!isset($data['template_id']) && !isset($data['type'])) {
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                userMessage: 'Tipo da escala é obrigatório quando não se usa template'
            );
        }

        return new BulkScheduleCreateDTO(
            quantity: $data['quantity'] ?? 1,
            nameBase: $data['name_base'] ?? '',
            type: isset($data['type']) ? ScheduleType::from($data['type']) : ScheduleType::GERAL,
            description: $data['description'] ?? '',
            local: $data['local'] ?? '',
            startTime: $data['start_time'] ?? '00:00',
            endTime: $data['end_time'] ?? '00:00',
            recurrence: RecurrenceType::from($data['recurrence']),
            areas: $data['areas'] ?? [],
            roleRequirements: $data['role_requirements'] ?? [],
            autoFill: $data['auto_fill'] ?? false,
            startDate: Carbon::parse($data['start_date']),
            templateId: $data['template_id'] ?? null,
            musicTemplateId: null
        );
    }
}

