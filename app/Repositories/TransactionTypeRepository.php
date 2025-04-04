<?php

namespace App\Repositories;

use App\Models\TransactionType;
use App\Repositories\Interfacies\TransactionTypeRepositoryInterface;

class TransactionTypeRepository implements TransactionTypeRepositoryInterface
{
    protected $model;

    public function __construct(TransactionType $model)
    {
        $this->model = $model;
    }

    public function getAll(): array
    {
        return $this->model
            ->orderBy('name')
            ->get()
            ->toArray();
    }
}
