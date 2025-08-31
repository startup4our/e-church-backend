<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Services\Interfaces\IMessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    protected IMessageService $service;

    public function __construct(IMessageService $service)
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
            'content' => 'required|string',
            'image_path' => 'nullable|string',
            'sent_at' => 'required|date',
            'chat_id' => 'required',
            'user_id' => 'required',
        ]);

        $message = $this->service->create($request->toArray());
        return response()->json($message, 201);
    }

    public function show($id)
    {
        return $this->service->get($id);
    }

    public function update(Request $request, Message $message)
    {
        $data = $request->validate([
            'content' => 'sometimes|required|string',
            'image_path' => 'nullable|string',
            'sent_at' => 'sometimes|required|date',
        ]);

        return $this->service->update($message, $data);
    }

    public function destroy(Message $message)
    {
        $this->service->delete($message);
        return response()->noContent();
    }
}
