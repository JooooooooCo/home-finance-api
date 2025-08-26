<?php

namespace App\Repositories\Budget;

use App\Models\Budget\Budget;

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

    public function findMostRecentUntilYearMonth(int $year, int $month): ?Budget
    {
        return $this->model
            ->where(function ($query) use ($year, $month) {
                $query->where('year', '<', $year)
                      ->orWhere(function ($subQuery) use ($year, $month) {
                          $subQuery->where('year', $year)
                                   ->where('month', '<=', $month);
                      });
            })
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->first();
    }

    public function findById(int $id): ?Budget
    {
        return $this->model
            ->where('id', $id)
            ->first();
    }
}
