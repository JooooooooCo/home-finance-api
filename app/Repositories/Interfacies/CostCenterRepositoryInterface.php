<?php

namespace App\Repositories\Interfacies;

use App\Models\CostCenter;

interface CostCenterRepositoryInterface
{
  public function getAll(int $userId): array;
  public function create(array $data): CostCenter;
  public function findById(int $userId, int $id):? CostCenter;
  public function update(int $userId, int $id, array $data): CostCenter;
  public function delete(int $userId, int $id): bool|null;
}
