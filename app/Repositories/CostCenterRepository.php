<?php

namespace App\Repositories;

use App\Models\CostCenter;
use App\Repositories\Interfacies\CostCenterRepositoryInterface;

class CostCenterRepository implements CostCenterRepositoryInterface
{
    protected $model;

    public function __construct(CostCenter $model)
    {
        $this->model = $model;
    }

    public function getAll(int $userId): array
    {
        return $this->model
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('id', $userId);
            })
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function create(array $data): CostCenter
    {
        return $this->model->create($data);
    }

    public function findById(int $userId, int $id): CostCenter
    {
        return $this->model
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('id', $userId);
            })
            ->findOrFail($id);
    }

    public function update(int $userId, int $id, array $data): CostCenter
    {
        $costCenter = $this->findById($userId, $id);
        $costCenter->fill($data)->save();
        return $costCenter;
    }

    public function delete(int $userId, int $id): bool|null
    {
        $costCenter = $this->findById($userId, $id);
        return $costCenter->delete();
    }
}
