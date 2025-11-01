<?php

namespace App\Repositories\Budget;

use App\Models\Budget\BudgetCategory;

class BudgetCategoryRepository
{
    protected $model;

    public function __construct(BudgetCategory $model)
    {
        $this->model = $model;
    }

    public function create(array $data): BudgetCategory
    {
        return $this->model->create($data);
    }

    public function listByBudgetClassificationId(int $budgetClassificationId): array
    {
        return $this->model
            ->with('category:id,name')
            ->where('budget_classification_id', $budgetClassificationId)
            ->get()
            ->toArray();
    }

    // public function update(int $id, array $data): BudgetCategory
    // {
    //     $model = $this->findById($id);
    //     $model->fill($data)->save();
    //     return $model;
    // }
}