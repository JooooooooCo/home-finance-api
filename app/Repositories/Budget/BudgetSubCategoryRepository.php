<?php

namespace App\Repositories\Budget;

use App\Models\Budget\BudgetSubCategory;

class BudgetSubCategoryRepository
{
    protected $model;

    public function __construct(BudgetSubCategory $model)
    {
        $this->model = $model;
    }

    public function create(array $data): BudgetSubCategory
    {
        return $this->model->create($data);
    }

    public function listByBudgetCategoryId(int $budgetCategoryId): array
    {
        return $this->model
            ->with('subCategory:id,name')
            ->where('budget_category_id', $budgetCategoryId)
            ->get()
            ->toArray();
    }

    // public function update(int $id, array $data): BudgetSubCategory
    // {
    //     $model = $this->findById($id);
    //     $model->fill($data)->save();
    //     return $model;
    // }
}