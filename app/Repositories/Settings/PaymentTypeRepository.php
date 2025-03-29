<?php

namespace App\Repositories\Settings;

use App\Models\Settings\PaymentType;
use App\Repositories\Settings\Interfacies\PaymentTypeRepositoryInterface;

class PaymentTypeRepository implements PaymentTypeRepositoryInterface
{
    protected $model;

    public function __construct(PaymentType $model)
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

    public function create(array $data): PaymentType
    {
        return $this->model->create($data);
    }

    public function findById(int $id): PaymentType
    {
        return $this->model->findOrFail($id);
    }

    public function update(int $id, array $data): PaymentType
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
