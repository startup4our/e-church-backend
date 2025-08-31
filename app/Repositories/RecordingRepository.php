<?php

namespace App\Repositories;

use App\Models\Recording;
use Illuminate\Database\Eloquent\Collection;

class RecordingRepository
{
    protected $model;

    public function __construct(Recording $recording)
    {
        $this->model = $recording;
    }

    public function create(array $data): Recording
    {
        return $this->model->create($data);
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function getById(int $id): Recording
    {
        return $this->model->findOrFail($id);
    }

    public function update(int $id, array $data): Recording
    {
        $recording = $this->model->findOrFail($id);
        $recording->update($data);
        return $recording;
    }

    public function delete(int $id): bool
    {
        $recording = $this->model->findOrFail($id);
        return $recording->delete();
    }
}
