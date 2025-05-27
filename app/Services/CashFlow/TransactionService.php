<?php

namespace App\Services\CashFlow;

use Exception;
use App\Models\CashFlow\Transaction;
use App\Models\Settings\PaymentStatusType;
use App\Models\TransactionType;
use App\Repositories\CashFlow\Interfacies\TransactionRepositoryInterface;

class TransactionService
{
    private const MAX_ALLOWED_LIST_RESPONSE = 20000;
    protected $repository;

    public function __construct(TransactionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function list(array $filters): array
    {
        $transactions = $this->repository->getAll($filters);

        if (empty($transactions)) {
            throw new Exception("Não há registros para o filtro aplicado", 404);
        }

        if (count($transactions) > self::MAX_ALLOWED_LIST_RESPONSE) {
            $msg = "O filtro aplicado retornou mais que o máximo permitido de " . self::MAX_ALLOWED_LIST_RESPONSE . " registros.";
            throw new Exception($msg, 422);
        }

        $endDate = isset($filters['dueDateRange'][0]) ? $filters['dueDateRange'][0] : now()->toDateString();
        $historyTotals = $this->repository->getHistoryExecutedAmount($endDate);

        $totals = $this->getTotals($transactions, $historyTotals);

        return [
            'transactions' => $transactions,
            'totals' => $totals,
        ];
    }

    private function getTotals($transactions, $historyTotals)
    {
        $totals = $this->calcFilteredTotals($transactions);
        $executedHistoryBalanceAmount = $this->calcExecutedHistoryBalanceAmount($historyTotals);
        $forecastBalanceAmount = ($executedHistoryBalanceAmount + $totals['forecast_revenue_amount']) - $totals['forecast_expense_amount'];
        $executedBalanceAmount = ($executedHistoryBalanceAmount + $totals['executed_revenue_amount']) - $totals['executed_expense_amount'];

        return [
            'executed_history_balance_amount' => round($executedHistoryBalanceAmount, 2),
            'forecast_balance_amount' => round($forecastBalanceAmount, 2),
            'executed_balance_amount' => round($executedBalanceAmount, 2),
            'forecast_expense_amount' => round($totals['forecast_expense_amount'], 2),
            'forecast_revenue_amount' => round($totals['forecast_revenue_amount'], 2),
        ];
    }

    private function calcFilteredTotals($transactions)
    {
        $totals = [
            'forecast_expense_amount' => 0,
            'executed_expense_amount' => 0,
            'forecast_revenue_amount' => 0,
            'executed_revenue_amount' => 0,
        ];

        foreach ($transactions as $transaction) {
            if ($transaction['transaction_type_id'] == TransactionType::EXPENSE) {
                $totals['forecast_expense_amount'] += $transaction['amount'];
                
                if ($transaction['payment_status_id'] == PaymentStatusType::PAID) {
                    $totals['executed_expense_amount'] += $transaction['amount'];
                }
            }

            if ($transaction['transaction_type_id'] == TransactionType::REVENUE) {
                $totals['forecast_revenue_amount'] += $transaction['amount'];
                
                if ($transaction['payment_status_id'] == PaymentStatusType::PAID) {
                    $totals['executed_revenue_amount'] += $transaction['amount'];
                }
            }
        }

        return $totals;
    }

    private function calcExecutedHistoryBalanceAmount($historyTotals)
    {
        $totals = [
            'executed_expense_amount' => 0,
            'executed_revenue_amount' => 0,
        ];

        foreach ($historyTotals as $historyTotal) {
            if ($historyTotal['transaction_type_id'] == TransactionType::EXPENSE 
                && $historyTotal['payment_status_id'] == PaymentStatusType::PAID) {
                $totals['executed_expense_amount'] += $historyTotal['amount'];
            }

            if ($historyTotal['transaction_type_id'] == TransactionType::REVENUE 
                && $historyTotal['payment_status_id'] == PaymentStatusType::PAID) {
                $totals['executed_revenue_amount'] += $historyTotal['amount'];
            }
        }

        return $totals['executed_revenue_amount'] - $totals['executed_expense_amount'] ;
    }

    public function create(array $data): Transaction
    {
        return $this->repository->create($data);
    }

    public function createBatch(array $transactions): array
    {        
        return array_map(function ($item) {
            return $this->repository->create($item);
        }, $transactions);
    }

    public function get(int $id): Transaction
    {
        return $this->repository->findById($id);
    }

    public function update(int $id, array $data): Transaction
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }
}
