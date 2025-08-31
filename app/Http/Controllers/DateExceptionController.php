<?php

namespace App\Http\Controllers;

use App\Models\DateException;
use App\Services\Interfaces\IDateExceptionService;
use Illuminate\Http\Request;

class DateExceptionController extends Controller
{
    protected IDateExceptionService $service;

    public function __construct(IDateExceptionService $service)
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
            'exception_date' => 'required|date',
            'shift' => 'required|in:morning,afternoon,night',
            'justification' => 'nullable|string',
            'user_id' => 'required'
        ]);

        $exception = $this->service->create($data);
        return response()->json($exception, 201);
    }

    public function show($id)
    {
        return $this->service->get($id);
    }

    public function update(Request $request, DateException $exception)
    {
        $data = $request->validate([
            'exception_date' => 'required|date',
            'shift' => 'required|in:morning,afternoon,night',
            'justification' => 'nullable|string',
        ]);

        return $this->service->update($exception, $data);
    }

    public function destroy(DateException $exception)
    {
        $this->service->delete($exception);
        return response()->noContent();
    }
}
