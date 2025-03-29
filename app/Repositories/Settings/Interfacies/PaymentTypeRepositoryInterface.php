<?php

namespace App\Repositories\Settings\Interfacies;

use App\Models\Settings\PaymentType;

interface PaymentTypeRepositoryInterface
{
  public function getAll(): array;
  public function create(array $data): PaymentType;
  public function findById(int $id):? PaymentType;
  public function update(int $id, array $data): PaymentType;
  public function delete(int $id): bool|null;
}
