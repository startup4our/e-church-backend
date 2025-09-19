<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\IChurchService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Log;

class ChurchController extends Controller
{
    public function __construct(private IChurchService $churchService) {}

    public function index()
    {
        return response()->json($this->churchService->getAll());
    }

    public function show(string $id)
    {
        return response()->json($this->churchService->getById($id));
    }

    public function update(Request $request, string $id)
    {
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

        return response()->json($this->churchService->update($id, $data));
    }

    public function destroy(string $id)
    {
        $this->churchService->delete($id);
        return response()->json(null, 204);
    }

    public function listChurchesForRegister()
    {
        Log::info('Getting all churches to register');
        return response()->json($this->churchService->getChurchesForRegister(), 200);
    }
}
