<?php

namespace App\Services\Settings;

use App\Models\Settings\SecondaryCategory;
use App\Repositories\Settings\Interfacies\SecondaryCategoryRepositoryInterface;

class SecondaryCategoryService
{
    protected $repository;

    public function __construct(SecondaryCategoryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function list(?string $type): array
    {
        return $this->repository->getAll($type);
    }

    public function create(array $data): SecondaryCategory
    {
        return $this->repository->create($data);
    }

    public function get(int $id): SecondaryCategory
    {
        return $this->repository->findById($id);
    }

    public function update(int $id, array $data): SecondaryCategory
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }
}
