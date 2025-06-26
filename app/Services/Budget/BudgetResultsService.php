<?php

namespace App\Services\Budget;

use App\Helpers\dateHelper;
use Exception;
use App\Repositories\Budget\BudgetRepository;
use App\Repositories\Budget\BudgetPrimaryCategoryRepository;
use App\Repositories\Budget\BudgetSpecificCategoryRepository;
use App\Repositories\Budget\BudgetSecondaryCategoryRepository;
use App\Repositories\CashFlow\Interfacies\TransactionRepositoryInterface;

class BudgetResultsService
{
    protected $repository;
    protected $primaryCategoryRepository;
    protected $secondaryCategoryRepository;
    protected $specificCategoryRepository;
    protected $transactionRepository;
    protected $budgetService;

    public function __construct(
        BudgetRepository $repository,
        BudgetPrimaryCategoryRepository $primaryCategoryRepository,
        BudgetSecondaryCategoryRepository $secondaryCategoryRepository,
        BudgetSpecificCategoryRepository $specificCategoryRepository,
        TransactionRepositoryInterface $transactionRepository,
        BudgetService $budgetService,
    ) {
        $this->repository = $repository;
        $this->primaryCategoryRepository = $primaryCategoryRepository;
        $this->secondaryCategoryRepository = $secondaryCategoryRepository;
        $this->specificCategoryRepository = $specificCategoryRepository;
        $this->transactionRepository = $transactionRepository;
        $this->budgetService = $budgetService;
    }

    public function get(array $filters)
    {
        if (!$filters['year'] || !$filters['month']) {
            throw new Exception("Informe o ano e mês", 422);
        }

        $filters = array_merge($filters, dateHelper::getMonthInitialEndDate($filters['year'], $filters['month']));

        $budget = $this->budgetService->get($filters);

        $budgetCategories = $this->indexCategoriesById($budget['categories']);
        
        $totalRevenue = $this->transactionRepository->getTotalRevenueForPeriod($filters['startDate'], $filters['endDate']);
        $transactions = $this->getExecutedExpenses($filters);
        $executedBudget = [];        

        foreach ($transactions as $transaction) {
            $executedBudget = $this->calculateResultsPrimaryCategory($executedBudget, $transaction, $budgetCategories, $totalRevenue);
            $executedBudget = $this->calculateResultsSecondaryCategory($executedBudget, $transaction, $budgetCategories);
            $executedBudget = $this->calculateResultsSpecificCategory($executedBudget, $transaction, $budgetCategories);
        }

        return $this->removeArrayAssocKeys($executedBudget);
    }

    private function getExecutedExpenses($filters)
    {
        
        return $this->transactionRepository->getAll([
            'transactionTypeIds' => [1],
            'paymentStatusIds' => [1],
            'dueDateRange' => [$filters['startDate'], $filters['endDate']],
        ]);
    }

    private function indexCategoriesById(array $categories)
    {
        $plainCategories = [];

        foreach ($categories as $category) {
            $categoryId = $category['id'];
            $plainCategories[$categoryId] = $category;
            
            if (!empty($category['children'])) {
                $plainCategories[$categoryId]['children'] = $this->indexCategoriesById($category['children']);
            }
        }

        return $plainCategories;
    }

    private function calculateResultsPrimaryCategory(array $executedBudget, array $transaction, array $budgetCategories, float $totalRevenue)
    {
        $primaryCategoryId = $transaction['primary_category_id'];
        $budgetPercentage = isset($budgetCategories[$primaryCategoryId]) ? $budgetCategories[$primaryCategoryId]['budget'] : 0;
        $budgetAmount =  $budgetPercentage ? round(($budgetPercentage / 100) * $totalRevenue, 2) : 0;

        if (isset($executedBudget[$primaryCategoryId])) {
            $executedAmount = $executedBudget[$primaryCategoryId]['executed_amount'] ?: 0;
            $executedAmount += (float) $transaction['amount'];
            $executedBudget[$primaryCategoryId]['executed_amount'] = round($executedAmount, 2);
            $executedBudget[$primaryCategoryId]['executed_percentage'] = $budgetAmount > 0 ? round(($executedAmount / $budgetAmount) * 100, 2) : 0;
            return $executedBudget;
        }
        
        $executedAmount = (float) $transaction['amount'];

        $executedBudget[$primaryCategoryId] = [
            'id' => $primaryCategoryId,
            'name' => $transaction['primary_category']['name'],
            'budget_amount' => $budgetAmount,
            'budget_percentage' => $budgetPercentage,
            'executed_amount' => round($executedAmount, 2),
            'executed_percentage' => $budgetAmount > 0 ? round(($executedAmount / $budgetAmount) * 100, 2) : 0,
            'children' => [],
        ];

        return $executedBudget;
    }

    private function calculateResultsSecondaryCategory(array $executedBudget, array $transaction, array $budgetCategories)
    {
        $primaryCategoryId = $transaction['primary_category_id'];
        $secondaryCategoryId = $transaction['secondary_category_id'];
        $baseBudgetAmount = isset($executedBudget[$primaryCategoryId]) ? $executedBudget[$primaryCategoryId]['budget_amount'] : 0;
        $budgetPercentage = isset($budgetCategories[$primaryCategoryId]['children'][$secondaryCategoryId]) ? 
            $budgetCategories[$primaryCategoryId]['children'][$secondaryCategoryId]['budget'] : 0;
        $budgetAmount =  $budgetPercentage ? round(($budgetPercentage / 100) * $baseBudgetAmount, 2) : 0;

        if (isset($executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId])) {
            $executedAmount = $executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['executed_amount'] ?: 0;
            $executedAmount += (float) $transaction['amount'];
            $executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['executed_amount'] = round($executedAmount, 2);
            $executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['executed_percentage'] = $budgetAmount > 0 ? round(($executedAmount / $budgetAmount) * 100, 2) : 0;
            return $executedBudget;
        }
        
        $executedAmount = (float) $transaction['amount'];

        $executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId] = [
            'id' => $secondaryCategoryId,
            'name' => $transaction['secondary_category']['name'],
            'budget_amount' => $budgetAmount,
            'budget_percentage' => $budgetPercentage,
            'executed_amount' => round($executedAmount, 2),
            'executed_percentage' => $budgetAmount > 0 ? round(($executedAmount / $budgetAmount) * 100, 2) : 0,
            'children' => [],
        ];

        return $executedBudget;
    }

    private function calculateResultsSpecificCategory(array $executedBudget, array $transaction, array $budgetCategories)
    {
        $primaryCategoryId = $transaction['primary_category_id'];
        $secondaryCategoryId = $transaction['secondary_category_id'];
            $specificCategoryId = $transaction['specific_category_id'];
        $baseBudgetAmount = isset($executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId])
            ? $executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['budget_amount'] : 0;
        $budgetPercentage = isset($budgetCategories[$primaryCategoryId]['children'][$secondaryCategoryId]['children'][$specificCategoryId])
            ? $budgetCategories[$primaryCategoryId]['children'][$secondaryCategoryId]['children'][$specificCategoryId]['budget'] : 0;
        $budgetAmount =  $budgetPercentage ? round(($budgetPercentage / 100) * $baseBudgetAmount, 2) : 0;

        if (isset($executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['children'][$specificCategoryId])) {
            $executedAmount = $executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['children'][$specificCategoryId]['executed_amount'] ?: 0;
            $executedAmount += (float) $transaction['amount'];
            $executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['children'][$specificCategoryId]['executed_amount'] = 
                round($executedAmount, 2);
            $executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['children'][$specificCategoryId]['executed_percentage'] = 
                $budgetAmount > 0 ? round(($executedAmount / $budgetAmount) * 100, 2) : 0;
            return $executedBudget;
        }
        
        $executedAmount = (float) $transaction['amount'];

        $executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['children'][$specificCategoryId] = [
            'id' => $specificCategoryId,
            'name' => $transaction['specific_category']['name'],
            'budget_amount' => $budgetAmount,
            'budget_percentage' => $budgetPercentage,
            'executed_amount' => round($executedAmount, 2),
            'executed_percentage' => $budgetAmount > 0 ? round(($executedAmount / $budgetAmount) * 100, 2) : 0,
            'children' => [],
        ];

        return $executedBudget;
    }

    private function removeArrayAssocKeys($executedBudget)
    {
        $executedBudget = array_values($executedBudget);

        foreach ($executedBudget as &$primaryCategory) {
            $primaryCategory['children'] = array_values($primaryCategory['children']);

            foreach ($primaryCategory['children'] as &$secondaryCategory) {
                $secondaryCategory['children'] = array_values($secondaryCategory['children']);
            }
        };

        return $executedBudget;
    }
}
