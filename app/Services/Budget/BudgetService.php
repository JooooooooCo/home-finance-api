<?php

namespace App\Services\Budget;

use Exception;
use App\Models\Budget\Budget;
use App\Repositories\Budget\BudgetRepository;
use App\Repositories\Budget\BudgetClassificationRepository;
use App\Repositories\Budget\BudgetSubCategoryRepository;
use App\Repositories\Budget\BudgetCategoryRepository;

class BudgetService
{
    protected $repository;
    protected $repositoryClassification;
    protected $repositoryCategory;
    protected $repositorySubCategory;

    public function __construct(
        BudgetRepository $repository,
        BudgetClassificationRepository $repositoryClassification,
        BudgetCategoryRepository $repositoryCategory,
        BudgetSubCategoryRepository $repositorySubCategory,
    ) {
        $this->repository = $repository;
        $this->repositoryClassification = $repositoryClassification;
        $this->repositoryCategory = $repositoryCategory;
        $this->repositorySubCategory = $repositorySubCategory;
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

        $this->repositoryClassification->deleteByBudgetId($budgetId);

        $this->storeCategories($data['categories'] ?? [], $budget);

        return $budget;
    }

    private function storeCategories(array $classifications, Budget $budget): void
    {
        foreach ($classifications as $classification) {
            $budgetClassification = $this->repositoryClassification->create([
                'budget_id' => $budget->id,
                'classification_id' => $classification['id'],
                'percentage' => $classification['budget'],
            ]);

            $categories = $classification['children'] ?? [];

            foreach ($categories as $category) {
                $budgetCategory = $this->repositoryCategory->create([
                    'budget_classification_id' => $budgetClassification->id,
                    'budget_id' => $budget->id,
                    'category_id' => $category['id'],
                    'percentage' => $category['budget'],
                ]);

                $subCategories = $category['children'] ?? [];

                foreach ($subCategories as $subCategory) {
                    $this->repositorySubCategory->create([
                        'budget_category_id' => $budgetCategory->id,
                        'budget_id' => $budget->id,
                        'sub_category_id' => $subCategory['id'],
                        'percentage' => $subCategory['budget'],
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

        $budget = $this->repository->findMostRecentUntilYearMonth($filters['year'], $filters['month']);

        if (empty($budget)) {
            return [];
        }
        
        $classifications = $this->repositoryClassification->listByBudgetId($budget->id);
        $classifications = array_map(function ($classification) {

            $categories = $this->repositoryCategory->listByBudgetClassificationId($classification['id']);
            $categories = array_map(function ($category) {

                $subCategories = $this->repositorySubCategory->listByBudgetCategoryId($category['id']);
                $subCategories = array_map(function ($subCategory) {
                    return [
                        'id' => $subCategory['sub_category']['id'],
                        'name' => $subCategory['sub_category']['name'],
                        'budget' => $subCategory['percentage'],
                    ];
                }, $subCategories);

                return [
                    'id' => $category['category']['id'],
                    'name' => $category['category']['name'],
                    'budget' => $category['percentage'],
                    'children' => $subCategories,
                ];
            }, $categories);

            return [
                'id' => $classification['classification']['id'],
                'name' => $classification['classification']['name'],
                'budget' => $classification['percentage'],
                'children' => $categories,
            ];

        }, $classifications);

        return [
            'id' => $budget->id,
            'year' => $budget->year,
            'month' => $budget->month,
            'categories' => $classifications,
        ];
    }
}
