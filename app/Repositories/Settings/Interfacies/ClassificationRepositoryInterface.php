<?php

namespace App\Repositories\Settings\Interfacies;

use App\Models\Settings\Classification;

interface ClassificationRepositoryInterface
{
  public function getAll(): array;
  public function create(array $data): Classification;
  public function findById(int $id):? Classification;
  public function update(int $id, array $data): Classification;
  public function delete(int $id): bool|null;
}