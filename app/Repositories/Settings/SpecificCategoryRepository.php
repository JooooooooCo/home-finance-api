<?php

namespace App\Repositories\Settings;

use App\Models\Settings\SpecificCategory;
use App\Repositories\Settings\Interfacies\SpecificCategoryRepositoryInterface;

class SpecificCategoryRepository implements SpecificCategoryRepositoryInterface
{
    protected $model;

    public function __construct(SpecificCategory $model)
    {
        $this->model = $model;
    }

    public function getAll(int $secondaryCategoryId): array
    {
        return $this->model
            ->where('secondary_category_id', $secondaryCategoryId)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function create(array $data): SpecificCategory
    {
        return $this->model->create($data);
    }

    public function findById(int $id): SpecificCategory
    {
        return $this->model->findOrFail($id);
    }

    public function update(int $id, array $data): SpecificCategory
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
