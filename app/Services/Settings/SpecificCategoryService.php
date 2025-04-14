<?php

namespace App\Services\Settings;

use Exception;
use App\Models\Settings\SpecificCategory;
use App\Repositories\Settings\Interfacies\SpecificCategoryRepositoryInterface;

class SpecificCategoryService
{
    protected $repository;

    public function __construct(SpecificCategoryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function list(int $secondaryCategoryId): array
    {
        if (empty($secondaryCategoryId)) {
            throw new Exception('Informe o secondaryCategoryId');
        }

        return $this->repository->getAll($secondaryCategoryId);
    }

    public function create(array $data): SpecificCategory
    {
        return $this->repository->create($data);
    }

    public function get(int $id): SpecificCategory
    {
        return $this->repository->findById($id);
    }

    public function update(int $id, array $data): SpecificCategory
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }
}
