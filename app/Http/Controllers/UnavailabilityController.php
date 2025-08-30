<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Unavailability;
use App\Services\Interfaces\IUnavailabilityService;
use Illuminate\Http\Request;

class UnavailabilityController extends Controller
{
    protected $service;

    public function __construct(IUnavailabilityService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return $this->service->listAll();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'weekday' => 'required|in:0,1,2,3,4,5,6',
            'shift'   => 'required|in:manha,tarde,noite',
        ]);

        $unavailability = $this->service->create($data);
        return response()->json($unavailability, 201);
    }

    public function show($id)
    {
        return $this->service->get($id);
    }

    public function update(Request $request, Unavailability $unavailability)
    {
        $data = $request->validate([
            'weekday' => 'required|in:0,1,2,3,4,5,6',
            'shift'   => 'required|in:manha,tarde,noite',
        ]);

        return $this->service->update($unavailability, $data);
    }

    public function destroy(Unavailability $unavailability)
    {
        $this->service->delete($unavailability);
        return response()->noContent();
    }
}
