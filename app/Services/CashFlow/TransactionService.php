<?php

namespace App\Services\CashFlow;

use App\Models\CashFlow\Transaction;
use App\Repositories\CashFlow\Interfacies\TransactionRepositoryInterface;

class TransactionService
{
    protected $repository;

    public function __construct(TransactionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function list(array $filters): array
    {
        return $this->repository->getAll($filters);
    }

    public function create(array $data): Transaction
    {
        return $this->repository->create($data);
    }

    public function createBatch(array $transactions): array
    {        
        return array_map(function ($item) {
            return $this->repository->create($item);
        }, $transactions);
    }

    public function get(int $id): Transaction
    {
        return $this->repository->findById($id);
    }

    public function update(int $id, array $data): Transaction
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }
}
