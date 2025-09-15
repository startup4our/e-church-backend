<?php

namespace App\Services\Interfaces;

use Illuminate\Support\Collection;


interface IChatService
{
    public function getAll();
    public function getById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function getChatsForUser(int $user_id, array $areas): Collection;
}
