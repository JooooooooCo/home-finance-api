<?php

namespace App\Repositories\Settings\Interfacies;

use App\Models\Settings\SpecificCategory;

interface SpecificCategoryRepositoryInterface
{
  public function getAll(int $secondaryCategoryId): array;
  public function create(array $data): SpecificCategory;
  public function findById(int $id):? SpecificCategory;
  public function update(int $id, array $data): SpecificCategory;
  public function delete(int $id): bool|null;
}
