<?php

namespace App\Services\Settings;

use App\Models\Settings\PaymentType;
use App\Repositories\Settings\Interfacies\PaymentTypeRepositoryInterface;

class PaymentTypeService
{
    protected $repository;

    public function __construct(PaymentTypeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function list(): array
    {
        return $this->repository->getAll();
    }

    public function create(array $data): PaymentType
    {
        return $this->repository->create($data);
    }

    public function get(int $id): PaymentType
    {
        return $this->repository->findById($id);
    }

    public function update(int $id, array $data): PaymentType
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }
}
