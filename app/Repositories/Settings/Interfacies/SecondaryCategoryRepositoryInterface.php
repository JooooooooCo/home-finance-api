<?php

namespace App\Repositories\Settings\Interfacies;

use App\Models\Settings\SecondaryCategory;

interface SecondaryCategoryRepositoryInterface
{
  public function getAll(?string $type): array;
  public function create(array $data): SecondaryCategory;
  public function findById(int $id):? SecondaryCategory;
  public function update(int $id, array $data): SecondaryCategory;
  public function delete(int $id): bool|null;
}
