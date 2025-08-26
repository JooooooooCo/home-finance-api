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

        if (!isset($budget['categories'])) {
            return [];
        }

        $budgetCategories = $this->indexCategoriesById($budget['categories']);

        $totalRevenue = $this->transactionRepository->getTotalRevenueForPeriod($filters['startDate'], $filters['endDate']);
        $transactions = $this->getExecutedExpenses($filters);
        $executedBudget = $this->initializeExecutedBudgetFromCategories($budget['categories'], $totalRevenue);

        foreach ($transactions as $transaction) {
            $executedBudget = $this->ensureTransactionCategoriesExist($executedBudget, $transaction);
            $executedBudget = $this->addTransactionToExecutedBudget($executedBudget, $transaction, $budgetCategories);
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

    private function initializeExecutedBudgetFromCategories(array $categories, float $totalRevenue): array
    {
        $executedBudget = [];
        
        foreach ($categories as $primaryCategory) {
            $primaryBudgetAmount = round(($primaryCategory['budget'] / 100) * $totalRevenue, 2);
            
            $executedBudget[$primaryCategory['id']] = [
                'id' => $primaryCategory['id'],
                'name' => $primaryCategory['name'],
                'budget_amount' => $primaryBudgetAmount,
                'budget_percentage' => $primaryCategory['budget'],
                'executed_amount' => 0,
                'executed_percentage' => 0,
                'children' => [],
            ];
            
            if (!empty($primaryCategory['children'])) {
                foreach ($primaryCategory['children'] as $secondaryCategory) {
                    $secondaryBudgetAmount = round(($secondaryCategory['budget'] / 100) * $primaryBudgetAmount, 2);
                    
                    $executedBudget[$primaryCategory['id']]['children'][$secondaryCategory['id']] = [
                        'id' => $secondaryCategory['id'],
                        'name' => $secondaryCategory['name'],
                        'budget_amount' => $secondaryBudgetAmount,
                        'budget_percentage' => $secondaryCategory['budget'],
                        'executed_amount' => 0,
                        'executed_percentage' => 0,
                        'children' => [],
                    ];
                    
                    if (!empty($secondaryCategory['children'])) {
                        foreach ($secondaryCategory['children'] as $specificCategory) {
                            $specificBudgetAmount = round(($specificCategory['budget'] / 100) * $secondaryBudgetAmount, 2);
                            
                            $executedBudget[$primaryCategory['id']]['children'][$secondaryCategory['id']]['children'][$specificCategory['id']] = [
                                'id' => $specificCategory['id'],
                                'name' => $specificCategory['name'],
                                'budget_amount' => $specificBudgetAmount,
                                'budget_percentage' => $specificCategory['budget'],
                                'executed_amount' => 0,
                                'executed_percentage' => 0,
                                'children' => [],
                            ];
                        }
                    }
                }
            }
        }
        
        return $executedBudget;
    }

    private function ensureTransactionCategoriesExist(array $executedBudget, array $transaction): array
    {
        $primaryCategoryId = $transaction['primary_category_id'];
        $secondaryCategoryId = $transaction['secondary_category_id'];
        $specificCategoryId = $transaction['specific_category_id'];

        if ($primaryCategoryId && !isset($executedBudget[$primaryCategoryId])) {
            $primaryName = $transaction['primary_category']['name'] ?? 'Unknown';
            $executedBudget[$primaryCategoryId] = [
                'id' => $primaryCategoryId,
                'name' => $primaryName,
                'budget_amount' => 0,
                'budget_percentage' => 0,
                'executed_amount' => 0,
                'executed_percentage' => 0,
                'children' => [],
            ];
        }

        if ($secondaryCategoryId && isset($executedBudget[$primaryCategoryId]) &&
            !isset($executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId])) {
            $secondaryName = $transaction['secondary_category']['name'] ?? 'Unknown';
            $executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId] = [
                'id' => $secondaryCategoryId,
                'name' => $secondaryName,
                'budget_amount' => 0,
                'budget_percentage' => 0,
                'executed_amount' => 0,
                'executed_percentage' => 0,
                'children' => [],
            ];
        }

        if ($specificCategoryId && isset($executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]) &&
            !isset($executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['children'][$specificCategoryId])) {
            $specificName = $transaction['specific_category']['name'] ?? 'Unknown';
            $executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['children'][$specificCategoryId] = [
                'id' => $specificCategoryId,
                'name' => $specificName,
                'budget_amount' => 0,
                'budget_percentage' => 0,
                'executed_amount' => 0,
                'executed_percentage' => 0,
                'children' => [],
            ];
        }

        return $executedBudget;
    }

    private function addTransactionToExecutedBudget(array $executedBudget, array $transaction, array $budgetCategories): array
    {
        $primaryCategoryId = $transaction['primary_category_id'];
        $secondaryCategoryId = $transaction['secondary_category_id'];
        $specificCategoryId = $transaction['specific_category_id'];
        $amount = (float) $transaction['amount'];

        if (isset($executedBudget[$primaryCategoryId])) {
            $executedBudget[$primaryCategoryId]['executed_amount'] += $amount;
            $executedBudget[$primaryCategoryId]['executed_amount'] = round($executedBudget[$primaryCategoryId]['executed_amount'], 2);

            if ($executedBudget[$primaryCategoryId]['budget_amount'] > 0) {
                $executedBudget[$primaryCategoryId]['executed_percentage'] = round(
                    ($executedBudget[$primaryCategoryId]['executed_amount'] / $executedBudget[$primaryCategoryId]['budget_amount']) * 100, 2
                );
            }
        }

        if ($secondaryCategoryId && isset($executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId])) {
            $executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['executed_amount'] += $amount;
            $executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['executed_amount'] =
                round($executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['executed_amount'], 2);

            if ($executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['budget_amount'] > 0) {
                $executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['executed_percentage'] = round(
                    ($executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['executed_amount'] /
                     $executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['budget_amount']) * 100, 2
                );
            }
        }

        if ($specificCategoryId && isset($executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['children'][$specificCategoryId])) {
            $executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['children'][$specificCategoryId]['executed_amount'] += $amount;
            $executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['children'][$specificCategoryId]['executed_amount'] =
                round($executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['children'][$specificCategoryId]['executed_amount'], 2);

            if ($executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['children'][$specificCategoryId]['budget_amount'] > 0) {
                $executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['children'][$specificCategoryId]['executed_percentage'] = round(
                    ($executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['children'][$specificCategoryId]['executed_amount'] /
                     $executedBudget[$primaryCategoryId]['children'][$secondaryCategoryId]['children'][$specificCategoryId]['budget_amount']) * 100, 2
                );
            }
        }

        return $executedBudget;
    }

}
