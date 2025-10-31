<?php

namespace App\Repositories\CashFlow\Interfacies;

use App\Models\CashFlow\Transaction;

interface TransactionRepositoryInterface
{
  public function getAll(array $filters): array;
  public function getHistoryExecutedAmount(String $endDate): array;
  public function getHistoryExecutedAmountByPaymentType(String $endDate, int $paymentTypeId): array;
  public function getMonthlyAmount(string $startDate, string $endDate): array;
  public function getMonthlyAmountByPaymentType(string $startDate, string $endDate, int $paymentTypeId): array;
  public function getTotalIncomeForPeriod(string $startDate, string $endDate): float;
  public function create(array $data): Transaction;
  public function findById(int $id):? Transaction;
  public function update(int $id, array $data): Transaction;
  public function delete(int $id): bool|null;
}
