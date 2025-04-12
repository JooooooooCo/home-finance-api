<?php

namespace App\Services\Settings;

use App\Models\Settings\PrimaryCategory;
use App\Repositories\Settings\Interfacies\PrimaryCategoryRepositoryInterface;

class PrimaryCategoryService
{
    protected $repository;

    public function __construct(PrimaryCategoryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function list(): array
    {
        return $this->repository->getAll();
    }

    public function create(array $data): PrimaryCategory
    {
        return $this->repository->create($data);
    }

    public function get(int $id): PrimaryCategory
    {
        return $this->repository->findById($id);
    }

    public function update(int $id, array $data): PrimaryCategory
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }
}
