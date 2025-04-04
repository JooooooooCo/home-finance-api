<?php

namespace App\Services;

use App\Repositories\Interfacies\TransactionTypeRepositoryInterface;

class TransactionTypeService
{
    protected $repository;

    public function __construct(TransactionTypeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function list(): array
    {
        return $this->repository->getAll();
    }
}
