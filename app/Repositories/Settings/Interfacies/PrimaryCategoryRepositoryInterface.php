<?php

namespace App\Repositories\Settings\Interfacies;

use App\Models\Settings\PrimaryCategory;

interface PrimaryCategoryRepositoryInterface
{
  public function getAll(): array;
  public function create(array $data): PrimaryCategory;
  public function findById(int $id):? PrimaryCategory;
  public function update(int $id, array $data): PrimaryCategory;
  public function delete(int $id): bool|null;
}
