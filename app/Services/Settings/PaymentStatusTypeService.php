<?php

namespace App\Services\Settings;

use App\Models\Settings\PaymentStatusType;
use App\Repositories\Settings\Interfacies\PaymentStatusTypeRepositoryInterface;

class PaymentStatusTypeService
{
    protected $repository;

    public function __construct(PaymentStatusTypeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function list(): array
    {
        return $this->repository->getAll();
    }

    public function create(array $data): PaymentStatusType
    {
        return $this->repository->create($data);
    }

    public function get(int $id): PaymentStatusType
    {
        return $this->repository->findById($id);
    }

    public function update(int $id, array $data): PaymentStatusType
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }
}
