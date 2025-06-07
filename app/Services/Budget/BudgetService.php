<?php

namespace App\Services\Budget;

use Exception;
use App\Models\Budget\Budget;
use App\Repositories\Budget\BudgetRepository;
use App\Repositories\Budget\BudgetPrimaryCategoryRepository;
use App\Repositories\Budget\BudgetSpecificCategoryRepository;
use App\Repositories\Budget\BudgetSecondaryCategoryRepository;

class BudgetService
{
    protected $repository;
    protected $repositoryPrimaryCategory;
    protected $repositorySecondaryCategory;
    protected $repositorySpecificCategory;

    public function __construct(
        BudgetRepository $repository,
        BudgetPrimaryCategoryRepository $repositoryPrimaryCategory,
        BudgetSecondaryCategoryRepository $repositorySecondaryCategory,
        BudgetSpecificCategoryRepository $repositorySpecificCategory,
    ) {
        $this->repository = $repository;
        $this->repositoryPrimaryCategory = $repositoryPrimaryCategory;
        $this->repositorySecondaryCategory = $repositorySecondaryCategory;
        $this->repositorySpecificCategory = $repositorySpecificCategory;
    }

    public function create(array $data): Budget
    {
        if (!$data['year'] || !$data['month']) {
            throw new Exception("Informe o ano e mês", 422);
        }
        
        if ($this->repository->findByYearMonth($data['year'], $data['month'])) {
            throw new Exception("Já existe budget definido para o ano e mês informado", 422);
        }

        $budget = $this->repository->create([
            'year' => $data['year'],
            'month' => $data['month'],
        ]);

        $this->storeCategories($data['categories'] ?? [], $budget);

        return $budget;
    }

    public function replaceCategories(int $budgetId, array $data): Budget
    {
        if (empty($budgetId)) {
            throw new Exception("Informe o ID do budget", 422);
        }

        $budget = $this->repository->findById($budgetId);

        if (empty($budget)) {
            throw new Exception("Budget não encontrado", 404);
        }

        $this->repositoryPrimaryCategory->deleteByBudgetId($budgetId);

        $this->storeCategories($data['categories'] ?? [], $budget);

        return $budget;
    }

    private function storeCategories(array $primaryCategories, Budget $budget): void
    {
        foreach ($primaryCategories as $primaryCategory) {
            $budgetPrimaryCategory = $this->repositoryPrimaryCategory->create([
                'budget_id' => $budget->id,
                'primary_category_id' => $primaryCategory['id'],
                'percentage' => $primaryCategory['budget'],
            ]);

            $secondaryCategories = $primaryCategory['children'] ?? [];

            foreach ($secondaryCategories as $secondaryCategory) {
                $budgetSecondaryCategory = $this->repositorySecondaryCategory->create([
                    'budget_primary_category_id' => $budgetPrimaryCategory->id,
                    'budget_id' => $budget->id,
                    'secondary_category_id' => $secondaryCategory['id'],
                    'percentage' => $secondaryCategory['budget'],
                ]);

                $specificCategories = $secondaryCategory['children'] ?? [];

                foreach ($specificCategories as $specificCategory) {
                    $this->repositorySpecificCategory->create([
                        'budget_secondary_category_id' => $budgetSecondaryCategory->id,
                        'budget_id' => $budget->id,
                        'specific_category_id' => $specificCategory['id'],
                        'percentage' => $specificCategory['budget'],
                    ]);
                }
            }
        }
    }

    public function get(array $filters)
    {
        if (!$filters['year'] || !$filters['month']) {
            throw new Exception("Informe o ano e mês", 422);
        }

        $budget = $this->repository->findByYearMonth($filters['year'], $filters['month']);

        if (empty($budget)) {
            return [];
        }
        
        $primaryCategories = $this->repositoryPrimaryCategory->listByBudgetId($budget->id);
        $primaryCategories = array_map(function ($primaryCategory) {

            $secondaryCategories = $this->repositorySecondaryCategory->listByBudgetPrimaryCategoryId($primaryCategory['id']);
            $secondaryCategories = array_map(function ($secondaryCategory) {

                $specificCategories = $this->repositorySpecificCategory->listByBudgetSecondaryCategoryId($secondaryCategory['id']);
                $specificCategories = array_map(function ($specificCategory) {
                    return [
                        'id' => $specificCategory['specific_category']['id'],
                        'name' => $specificCategory['specific_category']['name'],
                        'budget' => $specificCategory['percentage'],
                    ];
                }, $specificCategories);

                return [
                    'id' => $secondaryCategory['secondary_category']['id'],
                    'name' => $secondaryCategory['secondary_category']['name'],
                    'budget' => $secondaryCategory['percentage'],
                    'children' => $specificCategories,
                ];
            }, $secondaryCategories);

            return [
                'id' => $primaryCategory['primary_category']['id'],
                'name' => $primaryCategory['primary_category']['name'],
                'budget' => $primaryCategory['percentage'],
                'children' => $secondaryCategories,
            ];

        }, $primaryCategories);

        return [
            'id' => $budget->id,
            'year' => $budget->year,
            'month' => $budget->month,
            'categories' => $primaryCategories,
        ];
    }
}
