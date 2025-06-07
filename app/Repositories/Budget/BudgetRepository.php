<?php

namespace App\Repositories\Budget;

use App\Models\Budget\Budget;
use Illuminate\Database\Eloquent\Collection;

class BudgetRepository
{
    protected $model;

    public function __construct(Budget $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Budget
    {
        return $this->model->create($data);
    }

    public function findByYearMonth(int $year, int $month): ?Budget
    {
        return $this->model
            ->where('year', $year)
            ->where('month', $month)
            ->first();
    }

    // public function update(int $id, array $data): Budget
    // {
    //     $model = $this->findById($id);
    //     $model->fill($data)->save();
    //     return $model;
    // }
}
