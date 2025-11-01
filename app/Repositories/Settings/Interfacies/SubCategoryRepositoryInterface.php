<?php

namespace App\Repositories\Settings\Interfacies;

use App\Models\Settings\SubCategory;

interface SubCategoryRepositoryInterface
{
  public function getAll(int $categoryId): array;
  public function create(array $data): SubCategory;
  public function findById(int $id):? SubCategory;
  public function update(int $id, array $data): SubCategory;
  public function delete(int $id): bool|null;
}