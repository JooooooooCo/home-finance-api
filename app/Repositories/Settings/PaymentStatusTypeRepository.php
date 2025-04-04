<?php

namespace App\Repositories\Settings;

use App\Models\Settings\PaymentStatusType;
use App\Repositories\Settings\Interfacies\PaymentStatusTypeRepositoryInterface;

class PaymentStatusTypeRepository implements PaymentStatusTypeRepositoryInterface
{
    protected $model;

    public function __construct(PaymentStatusType $model)
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

    public function create(array $data): PaymentStatusType
    {
        return $this->model->create($data);
    }

    public function findById(int $id): PaymentStatusType
    {
        return $this->model->findOrFail($id);
    }

    public function update(int $id, array $data): PaymentStatusType
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
