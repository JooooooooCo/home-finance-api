<?php

namespace App\Repositories\CashFlow\Interfacies;

use App\Models\CashFlow\Transaction;

interface TransactionRepositoryInterface
{
  public function getAll(): array;
  public function create(array $data): Transaction;
  public function findById(int $id):? Transaction;
  public function update(int $id, array $data): Transaction;
  public function delete(int $id): bool|null;
}
