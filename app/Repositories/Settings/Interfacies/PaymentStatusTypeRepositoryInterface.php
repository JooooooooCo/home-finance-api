<?php

namespace App\Repositories\Settings\Interfacies;

use App\Models\Settings\PaymentStatusType;

interface PaymentStatusTypeRepositoryInterface
{
  public function getAll(): array;
  public function create(array $data): PaymentStatusType;
  public function findById(int $id):? PaymentStatusType;
  public function update(int $id, array $data): PaymentStatusType;
  public function delete(int $id): bool|null;
}
