<?php

namespace App\Repositories\CashFlow;

use App\Enums\TransactionType;
use App\Models\CashFlow\Transaction;
use App\Repositories\CashFlow\Interfacies\TransactionRepositoryInterface;

class TransactionRepository implements TransactionRepositoryInterface
{
    protected $model;

    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }

    public function getAll(array $filters): array
    {
        $query = $this->model->with([
            'paymentType:id,name',
            'primaryCategory:id,name',
            'secondaryCategory:id,name',
            'specificCategory:id,name',
        ]);

        $query = $this->applyFilters($query, $filters);

        $query->orderBy('due_date', 'asc')
            ->orderBy('type', 'desc')
            ->orderBy('payment_type_id', 'asc')
            ->orderBy('purchase_date', 'asc');

        return $query->get()->toArray();
    }

    // TODO: REMOVE "EXECUTED" FROM NAME, AS NOT FILTER ONLY PAID
    public function getHistoryExecutedAmount(String $endDate): array
    {
        return $this->model
            ->selectRaw('type, status, SUM(amount) as amount')
            ->where('due_date', '<', $endDate)
            ->groupBy('type')
            ->groupBy('status')
            ->get()
            ->toArray();
    }

    // TODO: REMOVE "EXECUTED" FROM NAME, AS NOT FILTER ONLY PAID
    public function getHistoryExecutedAmountByPaymentType(String $endDate, int $paymentTypeId): array
    {
        return $this->model
            ->selectRaw('type, status, SUM(amount) as amount')
            ->where('due_date', '<', $endDate)
            ->where('payment_type_id', $paymentTypeId)
            ->groupBy('type')
            ->groupBy('status')
            ->get()
            ->toArray();
    }

    private function applyFilters($query, array $filters)
    {
        // TODO: CHANGE TYPE TO PLURAL
        if (!empty($filters['type'])) {
            $query->whereIn('type', $filters['type']);
        }

        if (!empty($filters['paymentTypeIds'])) {
            $query->whereIn('payment_type_id', $filters['paymentTypeIds']);
        }

        if (!empty($filters['paymentStatuses'])) {
            $query->whereIn('status', $filters['paymentStatuses']);
        }

        if (!empty($filters['primaryCategoryId'])) {
            $query->where('primary_category_id', $filters['primaryCategoryId']);
        }

        if (!empty($filters['secondaryCategoryId'])) {
            $query->where('secondary_category_id', $filters['secondaryCategoryId']);
        }

        if (!empty($filters['specificCategoryId'])) {
            $query->where('specific_category_id', $filters['specificCategoryId']);
        }

        if (!empty($filters['description'])) {
            $query->whereRaw('LOWER(description) LIKE ?', ['%' . strtolower($filters['description']) . '%']);
        }

        if (isset($filters['amountMin']) && $filters['amountMin'] != "") {
            $query->where('amount', '>=', $filters['amountMin']);
        }

        if (isset($filters['amountMax']) && $filters['amountMax'] != "") {
            $query->where('amount', '<=', $filters['amountMax']);
        }

        if (!empty($filters['dueDateRange'])) {
            $query->whereBetween('due_date', [$filters['dueDateRange'][0], $filters['dueDateRange'][1]]);
        }

        if (!empty($filters['paymentDateRange'])) {
            $query->whereBetween('payment_date', [$filters['paymentDateRange'][0], $filters['paymentDateRange'][1]]);
        }

        if (!empty($filters['purchaseDateRange'])) {
            $query->whereBetween('purchase_date', [$filters['purchaseDateRange'][0], $filters['purchaseDateRange'][1]]);
        }

        $reconciled = isset($filters['reconciled']) && !$filters['reconciled'] ? false : true;
        $notReconciled = isset($filters['notReconciled']) && !$filters['notReconciled'] ? false : true;

        if ($reconciled && !$notReconciled) {
            $query->where('is_reconciled', 1);
        } elseif (!$reconciled && $notReconciled) {
            $query->where('is_reconciled', 0);
        } elseif (!$reconciled && !$notReconciled) {
          $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public function create(array $data): Transaction
    {
        return $this->model->create($data);
    }

    public function findById(int $id): Transaction
    {
        return $this->model->findOrFail($id);
    }

    public function update(int $id, array $data): Transaction
    {
        $model = $this->findById($id);
        $model->fill($data)->save();
        return $model;
    }

    public function delete(int $id): bool|null
    {
        $model = $this->findById($id);
        return $model->delete();
    }

    public function getTotalIncomeForPeriod(string $startDate, string $endDate): float
    {
        return (float) $this->model
            ->whereBetween('due_date', [$startDate, $endDate])
            ->where('type', TransactionType::INCOME->value)
            ->sum('amount');
    }

    public function getMonthlyAmount(string $startDate, string $endDate): array
    {
        return $this->model
            ->selectRaw("DATE_FORMAT(due_date, '%Y-%m') AS `year_month`, type, SUM(amount) AS amount")
            ->whereBetween('due_date', [$startDate, $endDate])
            ->groupBy('year_month', 'type')
            ->orderBy('year_month')
            ->get()
            ->toArray();
    }

    public function getMonthlyAmountByPaymentType(string $startDate, string $endDate, int $paymentTypeId): array
    {
        return $this->model
            ->selectRaw("DATE_FORMAT(due_date, '%Y-%m') AS `year_month`, type, SUM(amount) AS amount")
            ->whereBetween('due_date', [$startDate, $endDate])
            ->where('payment_type_id', $paymentTypeId)
            ->groupBy('year_month', 'type')
            ->orderBy('year_month')
            ->get()
            ->toArray();
    }
}
