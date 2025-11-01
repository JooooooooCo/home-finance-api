<?php

namespace App\Repositories\Budget;

use App\Models\Budget\BudgetClassification;

class BudgetClassificationRepository
{
    protected $model;

    public function __construct(BudgetClassification $model)
    {
        $this->model = $model;
    }

    public function create(array $data): BudgetClassification
    {
        return $this->model->create($data);
    }

    public function listByBudgetId(int $budgetId): array
    {
        return $this->model        
            ->with('classification:id,name')
            ->where('budget_id', $budgetId)
            ->get()
            ->toArray();
    }

    public function deleteByBudgetId(int $budgetId): void
    {
        $this->model
            ->where('budget_id', $budgetId)
            ->delete();
    }
}