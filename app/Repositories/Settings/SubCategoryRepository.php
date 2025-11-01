<?php

namespace App\Repositories\Settings;

use App\Models\Settings\SubCategory;
use App\Repositories\Settings\Interfacies\SubCategoryRepositoryInterface;

class SubCategoryRepository implements SubCategoryRepositoryInterface
{
    protected $model;

    public function __construct(SubCategory $model)
    {
        $this->model = $model;
    }

    public function getAll(int $categoryId): array
    {
        return $this->model
            ->where('category_id', $categoryId)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function create(array $data): SubCategory
    {
        return $this->model->create($data);
    }

    public function findById(int $id): SubCategory
    {
        return $this->model->findOrFail($id);
    }

    public function update(int $id, array $data): SubCategory
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