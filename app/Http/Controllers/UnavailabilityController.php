<?php

namespace App\Http\Controllers;

use App\Models\Unavailability;
use Illuminate\Http\Request;


class UnavailabilityController extends Controller
{

    public function index()
    {
        return Unavailability::with('user')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'weekday' => 'required|in:0,1,2,3,4,5,6',
            'shift' => 'required|in:manha,tarde,noite',
        ]);

        $existing = Unavailability::where($data)->first();

        if ($existing) {
            return response()->json(['message' => 'Usuário já escalado para nesse dia/turno'], 409);
        }

        $unavailability = Unavailability::create($data);
        return response()->json($unavailability, 201);
    }

    public function show(Unavailability $unavailability) {
        return $unavailability->load('user');
    }

    public function update(Request $request, Unavailability $unavailability) {
        $data = $request->validade([
            'weekday' => 'required|in:0,1,2,3,4,5,6',
            'shift' => 'required|in:manha,tarde,noite',
        ]);

        $exists = Unavailability::where('user_id', $unavailability->user_id)
        ->where('weekday', $data['weekday'])
        ->where('shift', $data['shift'])
        ->where('id', '!=', $unavailability->id)
        ->exists();

        if ($exists) {
            return response()->json(['message' => 'Usuário já escalado para nesse dia/turno'], 409);
        }

        $unavailability->update($data);
        return $unavailability;
    }

    public function destroy(Unavailability $unavailability) {
        $unavailability->delete();
        return response()->noContent(); 
       }
}
