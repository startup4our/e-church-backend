<?php

namespace App\Repositories;

use App\Models\Chat;

class ChatRepository
{
    protected $model;

    public function __construct(Chat $chat)
    {
        $this->model = $chat;
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $chat = $this->getById($id);
        $chat->update($data);
        return $chat;
    }

    public function delete($id)
    {
        $chat = $this->getById($id);
        return $chat->delete();
    }
}
