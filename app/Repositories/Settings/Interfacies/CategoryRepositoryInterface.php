<?php

namespace App\Repositories\Settings\Interfacies;

use App\Models\Settings\Category;

interface CategoryRepositoryInterface
{
  public function getAll(?string $type): array;
  public function create(array $data): Category;
  public function findById(int $id):? Category;
  public function update(int $id, array $data): Category;
  public function delete(int $id): bool|null;
}