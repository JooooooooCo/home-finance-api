<?php

namespace App\Repositories\CashFlow;

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
            'transactionType:id,name',
            'paymentType:id,name',
            'paymentStatus:id,name',
            'primaryCategory:id,name',
            'secondaryCategory:id,name',
            'specificCategory:id,name',
        ]);

        if (!empty($filters['transactionTypeIds'])) {
            $query->whereIn('transaction_type_id', $filters['transactionTypeIds']);
        }

        if (!empty($filters['paymentTypeIds'])) {
            $query->whereIn('payment_type_id', $filters['paymentTypeIds']);
        }

        if (!empty($filters['paymentStatusIds'])) {
            $query->whereIn('payment_status_id', $filters['paymentStatusIds']);
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

        if ($filters['amountMin'] != "") {
            $query->where('amount', '>=', $filters['amountMin']);
        }

        if ($filters['amountMax'] != "") {
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

        $reconciled = $filters['reconciled'] ? true : false;
        $notReconciled = $filters['notReconciled'] ? true : false;

        if ($reconciled && !$notReconciled) {
            $query->where('is_reconciled', 1);
        } elseif (!$reconciled && $notReconciled) {
            $query->where('is_reconciled', 0);
        } elseif (!$reconciled && !$notReconciled) {
          $query->whereRaw('1 = 0');
        }

        return $query
            ->orderBy('id')
            ->get()
            ->toArray();
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
}
