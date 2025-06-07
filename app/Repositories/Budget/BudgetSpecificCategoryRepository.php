<?php

namespace App\Repositories\Budget;

use App\Models\Budget\BudgetSpecificCategory;

class BudgetSpecificCategoryRepository
{
    protected $model;

    public function __construct(BudgetSpecificCategory $model)
    {
        $this->model = $model;
    }

    public function create(array $data): BudgetSpecificCategory
    {
        return $this->model->create($data);
    }

    public function listByBudgetSecondaryCategoryId(int $budgetSecondaryCategoryId): array
    {
        return $this->model
            ->with('specificCategory:id,name')
            ->where('budget_secondary_category_id', $budgetSecondaryCategoryId)
            ->get()
            ->toArray();
    }

    // public function update(int $id, array $data): BudgetSpecificCategory
    // {
    //     $model = $this->findById($id);
    //     $model->fill($data)->save();
    //     return $model;
    // }
}
