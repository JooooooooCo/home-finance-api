<?php

namespace App\Repositories\Settings;

use App\Models\Settings\Category;
use App\Repositories\Settings\Interfacies\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    protected $model;

    public function __construct(Category $model)
    {
        $this->model = $model;
    }

    public function getAll(?string $type): array
    {
        return $this->model
            ->when($type !== null, function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function create(array $data): Category
    {
        return $this->model->create($data);
    }

    public function findById(int $id): Category
    {
        return $this->model->findOrFail($id);
    }

    public function update(int $id, array $data): Category
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