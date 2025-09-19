<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Services\Interfaces\IChurchService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Log;

class ChurchController extends Controller
{
    public function __construct(private IChurchService $churchService) {}

    public function index()
    {
        try {
            $churches = $this->churchService->getAll();
            return response()->json([
                'success' => true,
                'data' => $churches
            ]);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function show(string $id)
    {
        try {
            $church = $this->churchService->getById($id);
            return response()->json([
                'success' => true,
                'data' => $church
            ]);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::CHURCH_NOT_FOUND,
                userMessage: 'Igreja não encontrada'
            );
        }
    }

    public function store(Request $request)
    {
        try {
            $ufs = 'AC,AL,AP,AM,BA,CE,DF,ES,GO,MA,MT,MS,MG,PA,PB,PR,PE,PI,RJ,RN,RS,RO,RR,SC,SP,SE,TO';

            $data = $request->validate([
                'name'       => ['required','string','max:120'],
                'cep'        => ['nullable','regex:/^\d{5}-?\d{3}$/'],
                'street'     => ['nullable','string','max:160'],
                'number'     => ['nullable','string','max:10'],
                'complement' => ['nullable','string','max:160'],
                'quarter'    => ['nullable','string','max:120'],
                'city'       => ['nullable','string','max:120'],
                'state'      => ['nullable',"in:$ufs"],
            ]);

            $church = $this->churchService->create($data);
            return response()->json([
                'success' => true,
                'data' => $church
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $ufs = 'AC,AL,AP,AM,BA,CE,DF,ES,GO,MA,MT,MS,MG,PA,PB,PR,PE,PI,RJ,RN,RS,RO,RR,SC,SP,SE,TO';

            $data = $request->validate([
                'name'       => ['sometimes','string','max:120'],
                'cep'        => ['nullable','regex:/^\d{5}-?\d{3}$/'],
                'street'     => ['nullable','string','max:160'],
                'number'     => ['nullable','string','max:10'],
                'complement' => ['nullable','string','max:160'],
                'quarter'    => ['sometimes','string','max:120'],
                'city'       => ['nullable','string','max:120'],
                'state'      => ['nullable',"in:$ufs"],
            ]);

            $church = $this->churchService->update($id, $data);
            return response()->json([
                'success' => true,
                'data' => $church
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->churchService->delete($id);
            return response()->json([
                'success' => true,
                'data' => null
            ], 204);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function listChurchesForRegister()
    {
        try {
            Log::info('Getting all churches to register');
            $churches = $this->churchService->getChurchesForRegister();
            return response()->json([
                'success' => true,
                'data' => $churches
            ], 200);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }
}
