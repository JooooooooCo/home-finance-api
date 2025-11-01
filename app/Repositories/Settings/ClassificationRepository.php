<?php

namespace App\Repositories\Settings;

use App\Models\Settings\Classification;
use App\Repositories\Settings\Interfacies\ClassificationRepositoryInterface;

class ClassificationRepository implements ClassificationRepositoryInterface
{
    protected $model;

    public function __construct(Classification $model)
    {
        $this->model = $model;
    }

    public function getAll(): array
    {
        return $this->model
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function create(array $data): Classification
    {
        return $this->model->create($data);
    }

    public function findById(int $id): Classification
    {
        return $this->model->findOrFail($id);
    }

    public function update(int $id, array $data): Classification
    {
        $model = $this->findById($id);
        $model->fill($data)->save();
        return $model;
    }

    public function delete(int $id): bool|null
    {
        $model = $this->findById($id);
        return $model->delete();
    }
}