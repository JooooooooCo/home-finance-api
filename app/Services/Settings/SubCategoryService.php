<?php

namespace App\Services\Settings;

use Exception;
use App\Models\Settings\SubCategory;
use App\Repositories\Settings\Interfacies\SubCategoryRepositoryInterface;

class SubCategoryService
{
    protected $repository;

    public function __construct(SubCategoryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function list(int $categoryId): array
    {
        if (empty($categoryId)) {
            throw new Exception('Informe o categoryId');
        }

        return $this->repository->getAll($categoryId);
    }

    public function create(array $data): SubCategory
    {
        return $this->repository->create($data);
    }

    public function get(int $id): SubCategory
    {
        return $this->repository->findById($id);
    }

    public function update(int $id, array $data): SubCategory
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }
}