<?php

namespace App\Services\Settings;

use App\Models\Settings\Category;
use App\Repositories\Settings\Interfacies\CategoryRepositoryInterface;

class CategoryService
{
    protected $repository;

    public function __construct(CategoryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function list(?string $type): array
    {
        return $this->repository->getAll($type);
    }

    public function create(array $data): Category
    {
        return $this->repository->create($data);
    }

    public function get(int $id): Category
    {
        return $this->repository->findById($id);
    }

    public function update(int $id, array $data): Category
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }
}