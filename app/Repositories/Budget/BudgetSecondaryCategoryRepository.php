<?php

namespace App\Repositories\Budget;

use App\Models\Budget\BudgetSecondaryCategory;

class BudgetSecondaryCategoryRepository
{
    protected $model;

    public function __construct(BudgetSecondaryCategory $model)
    {
        $this->model = $model;
    }

    public function create(array $data): BudgetSecondaryCategory
    {
        return $this->model->create($data);
    }

    public function listByBudgetPrimaryCategoryId(int $budgetPrimaryCategoryId): array
    {
        return $this->model
            ->with('secondaryCategory:id,name')
            ->where('budget_primary_category_id', $budgetPrimaryCategoryId)
            ->get()
            ->toArray();
    }

    // public function update(int $id, array $data): BudgetSecondaryCategory
    // {
    //     $model = $this->findById($id);
    //     $model->fill($data)->save();
    //     return $model;
    // }
}
