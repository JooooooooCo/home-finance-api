<?php

namespace App\Repositories\CashFlow;

use App\Models\CashFlow\Transaction;
use App\Repositories\CashFlow\Interfacies\TransactionRepositoryInterface;

class TransactionRepository implements TransactionRepositoryInterface
{
    protected $model;

    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }

    public function getAll(): array
    {
        return $this->model
            ->orderBy('id')
            ->get()
            ->toArray();
    }

    public function create(array $data): Transaction
    {
        return $this->model->create($data);
    }

    public function findById(int $id): Transaction
    {
        return $this->model->findOrFail($id);
    }

    public function update(int $id, array $data): Transaction
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
