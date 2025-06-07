<?php

namespace App\Repositories\Budget;

use App\Models\Budget\BudgetPrimaryCategory;

class BudgetPrimaryCategoryRepository
{
    protected $model;

    public function __construct(BudgetPrimaryCategory $model)
    {
        $this->model = $model;
    }

    public function create(array $data): BudgetPrimaryCategory
    {
        return $this->model->create($data);
    }

    public function listByBudgetId(int $budgetId): array
    {
        return $this->model        
            ->with('primaryCategory:id,name')
            ->where('budget_id', $budgetId)
            ->get()
            ->toArray();
    }

    // public function update(int $id, array $data): BudgetPrimaryCategory
    // {
    //     $model = $this->findById($id);
    //     $model->fill($data)->save();
    //     return $model;
    // }
}
