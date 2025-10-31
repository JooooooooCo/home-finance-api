<?php

namespace App\Repositories\Settings;

use App\Models\Settings\SecondaryCategory;
use App\Repositories\Settings\Interfacies\SecondaryCategoryRepositoryInterface;

class SecondaryCategoryRepository implements SecondaryCategoryRepositoryInterface
{
    protected $model;

    public function __construct(SecondaryCategory $model)
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

    public function create(array $data): SecondaryCategory
    {
        return $this->model->create($data);
    }

    public function findById(int $id): SecondaryCategory
    {
        return $this->model->findOrFail($id);
    }

    public function update(int $id, array $data): SecondaryCategory
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
