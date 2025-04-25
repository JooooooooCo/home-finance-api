<?php

namespace App\Services\CashFlow;

use Exception;
use App\Models\CashFlow\Transaction;
use App\Repositories\CashFlow\Interfacies\TransactionRepositoryInterface;

class TransactionService
{
    private const MAX_ALLOWED_LIST_RESPONSE = 1000;
    protected $repository;

    public function __construct(TransactionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function list(array $filters): array
    {
        $transactions = $this->repository->getAll($filters);

        if (empty($transactions)) {
            throw new Exception("Não há registros para o filtro aplicado", 404);
        }

        if (count($transactions) > self::MAX_ALLOWED_LIST_RESPONSE) {
            $msg = "O filtro aplicado retornou mais que o máximo permitido de " . self::MAX_ALLOWED_LIST_RESPONSE . " registros.";
            throw new Exception($msg, 422);
        }

        return $transactions;
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
