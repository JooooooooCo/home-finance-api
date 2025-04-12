<?php

namespace App\Repositories\Settings;

use App\Models\Settings\PrimaryCategory;
use App\Repositories\Settings\Interfacies\PrimaryCategoryRepositoryInterface;

class PrimaryCategoryRepository implements PrimaryCategoryRepositoryInterface
{
    protected $model;

    public function __construct(PrimaryCategory $model)
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

    public function create(array $data): PrimaryCategory
    {
        return $this->model->create($data);
    }

    public function findById(int $id): PrimaryCategory
    {
        return $this->model->findOrFail($id);
    }

    public function update(int $id, array $data): PrimaryCategory
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
