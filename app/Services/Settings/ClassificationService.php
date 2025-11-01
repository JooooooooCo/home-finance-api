<?php

namespace App\Services\Settings;

use App\Models\Settings\Classification;
use App\Repositories\Settings\Interfacies\ClassificationRepositoryInterface;

class ClassificationService
{
    protected $repository;

    public function __construct(ClassificationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function list(): array
    {
        return $this->repository->getAll();
    }

    public function create(array $data): Classification
    {
        return $this->repository->create($data);
    }

    public function get(int $id): Classification
    {
        return $this->repository->findById($id);
    }

    public function update(int $id, array $data): Classification
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }
}