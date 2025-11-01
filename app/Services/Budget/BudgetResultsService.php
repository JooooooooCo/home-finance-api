<?php

namespace App\Services\Budget;

use App\Enums\TransactionType;
use App\Helpers\dateHelper;
use Exception;
use App\Repositories\Budget\BudgetRepository;
use App\Repositories\Budget\BudgetClassificationRepository;
use App\Repositories\Budget\BudgetSubCategoryRepository;
use App\Repositories\Budget\BudgetCategoryRepository;
use App\Repositories\CashFlow\Interfacies\TransactionRepositoryInterface;

class BudgetResultsService
{
    protected $repository;
    protected $classificationRepository;
    protected $categoryRepository;
    protected $subCategoryRepository;
    protected $transactionRepository;
    protected $budgetService;

    public function __construct(
        BudgetRepository $repository,
        BudgetClassificationRepository $classificationRepository,
        BudgetCategoryRepository $categoryRepository,
        BudgetSubCategoryRepository $subCategoryRepository,
        TransactionRepositoryInterface $transactionRepository,
        BudgetService $budgetService,
    ) {
        $this->repository = $repository;
        $this->classificationRepository = $classificationRepository;
        $this->categoryRepository = $categoryRepository;
        $this->subCategoryRepository = $subCategoryRepository;
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

        $totalIncome = $this->transactionRepository->getTotalIncomeForPeriod($filters['startDate'], $filters['endDate']);
        $transactions = $this->getExecutedExpenses($filters);
        $executedBudget = $this->initializeExecutedBudgetFromCategories($budget['categories'], $totalIncome);

        foreach ($transactions as $transaction) {
            $executedBudget = $this->ensureTransactionCategoriesExist($executedBudget, $transaction);
            $executedBudget = $this->addTransactionToExecutedBudget($executedBudget, $transaction, $budgetCategories);
        }

        return $this->removeArrayAssocKeys($executedBudget);
    }

    private function getExecutedExpenses($filters)
    {
        
        return $this->transactionRepository->getAll([
            'type' => [TransactionType::EXPENSE->value],
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

        foreach ($executedBudget as &$classification) {
            $classification['children'] = array_values($classification['children']);

            foreach ($classification['children'] as &$category) {
                $category['children'] = array_values($category['children']);
            }
        };

        return $executedBudget;
    }

    private function initializeExecutedBudgetFromCategories(array $categories, float $totalIncome): array
    {
        $executedBudget = [];
        
        foreach ($categories as $classification) {
            $classificationBudgetAmount = round(($classification['budget'] / 100) * $totalIncome, 2);
            
            $executedBudget[$classification['id']] = [
                'id' => $classification['id'],
                'name' => $classification['name'],
                'budget_amount' => $classificationBudgetAmount,
                'budget_percentage' => $classification['budget'],
                'executed_amount' => 0,
                'executed_percentage' => 0,
                'children' => [],
            ];
            
            if (!empty($classification['children'])) {
                foreach ($classification['children'] as $category) {
                    $categoryBudgetAmount = round(($category['budget'] / 100) * $classificationBudgetAmount, 2);
                    
                    $executedBudget[$classification['id']]['children'][$category['id']] = [
                        'id' => $category['id'],
                        'name' => $category['name'],
                        'budget_amount' => $categoryBudgetAmount,
                        'budget_percentage' => $category['budget'],
                        'executed_amount' => 0,
                        'executed_percentage' => 0,
                        'children' => [],
                    ];
                    
                    if (!empty($category['children'])) {
                        foreach ($category['children'] as $subCategory) {
                            $subCategoryBudgetAmount = round(($subCategory['budget'] / 100) * $categoryBudgetAmount, 2);
                            
                            $executedBudget[$classification['id']]['children'][$category['id']]['children'][$subCategory['id']] = [
                                'id' => $subCategory['id'],
                                'name' => $subCategory['name'],
                                'budget_amount' => $subCategoryBudgetAmount,
                                'budget_percentage' => $subCategory['budget'],
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
        $classificationId = $transaction['classification_id'];
        $categoryId = $transaction['category_id'];
        $subCategoryId = $transaction['sub_category_id'];

        if ($classificationId && !isset($executedBudget[$classificationId])) {
            $classificationName = $transaction['classification']['name'] ?? 'Unknown';
            $executedBudget[$classificationId] = [
                'id' => $classificationId,
                'name' => $classificationName,
                'budget_amount' => 0,
                'budget_percentage' => 0,
                'executed_amount' => 0,
                'executed_percentage' => 0,
                'children' => [],
            ];
        }

        if ($categoryId && isset($executedBudget[$classificationId]) &&
            !isset($executedBudget[$classificationId]['children'][$categoryId])) {
            $categoryName = $transaction['category']['name'] ?? 'Unknown';
            $executedBudget[$classificationId]['children'][$categoryId] = [
                'id' => $categoryId,
                'name' => $categoryName,
                'budget_amount' => 0,
                'budget_percentage' => 0,
                'executed_amount' => 0,
                'executed_percentage' => 0,
                'children' => [],
            ];
        }

        if ($subCategoryId && isset($executedBudget[$classificationId]['children'][$categoryId]) &&
            !isset($executedBudget[$classificationId]['children'][$categoryId]['children'][$subCategoryId])) {
            $subCategoryName = $transaction['sub_category']['name'] ?? 'Unknown';
            $executedBudget[$classificationId]['children'][$categoryId]['children'][$subCategoryId] = [
                'id' => $subCategoryId,
                'name' => $subCategoryName,
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
        $classificationId = $transaction['classification_id'];
        $categoryId = $transaction['category_id'];
        $subCategoryId = $transaction['sub_category_id'];
        $amount = (float) $transaction['amount'];

        if (isset($executedBudget[$classificationId])) {
            $executedBudget[$classificationId]['executed_amount'] += $amount;
            $executedBudget[$classificationId]['executed_amount'] = round($executedBudget[$classificationId]['executed_amount'], 2);

            if ($executedBudget[$classificationId]['budget_amount'] > 0) {
                $executedBudget[$classificationId]['executed_percentage'] = round(
                    ($executedBudget[$classificationId]['executed_amount'] / $executedBudget[$classificationId]['budget_amount']) * 100, 2
                );
            }
        }

        if ($categoryId && isset($executedBudget[$classificationId]['children'][$categoryId])) {
            $executedBudget[$classificationId]['children'][$categoryId]['executed_amount'] += $amount;
            $executedBudget[$classificationId]['children'][$categoryId]['executed_amount'] =
                round($executedBudget[$classificationId]['children'][$categoryId]['executed_amount'], 2);

            if ($executedBudget[$classificationId]['children'][$categoryId]['budget_amount'] > 0) {
                $executedBudget[$classificationId]['children'][$categoryId]['executed_percentage'] = round(
                    ($executedBudget[$classificationId]['children'][$categoryId]['executed_amount'] /
                     $executedBudget[$classificationId]['children'][$categoryId]['budget_amount']) * 100, 2
                );
            }
        }

        if ($subCategoryId && isset($executedBudget[$classificationId]['children'][$categoryId]['children'][$subCategoryId])) {
            $executedBudget[$classificationId]['children'][$categoryId]['children'][$subCategoryId]['executed_amount'] += $amount;
            $executedBudget[$classificationId]['children'][$categoryId]['children'][$subCategoryId]['executed_amount'] =
                round($executedBudget[$classificationId]['children'][$categoryId]['children'][$subCategoryId]['executed_amount'], 2);

            if ($executedBudget[$classificationId]['children'][$categoryId]['children'][$subCategoryId]['budget_amount'] > 0) {
                $executedBudget[$classificationId]['children'][$categoryId]['children'][$subCategoryId]['executed_percentage'] = round(
                    ($executedBudget[$classificationId]['children'][$categoryId]['children'][$subCategoryId]['executed_amount'] /
                     $executedBudget[$classificationId]['children'][$categoryId]['children'][$subCategoryId]['budget_amount']) * 100, 2
                );
            }
        }

        return $executedBudget;
    }

}
