<?php

namespace App\Services\CashFlow;

use Exception;
use App\Models\CashFlow\Transaction;
use App\Models\Settings\PaymentStatusType;
use App\Models\TransactionType;
use App\Repositories\CashFlow\Interfacies\TransactionRepositoryInterface;
use App\Exports\TransactionExport;
use Maatwebsite\Excel\Facades\Excel;

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

    public function export(array $filters)
    {
        // TODO: criar factory
        $transactions = $this->repository->getAll($filters);
        $transactions = $this->mapTransactionsToExport($transactions);
        $export = new TransactionExport($transactions);
        return Excel::download($export, 'transactions.xlsx');
    }

    private function mapTransactionsToExport(array $transactions)
    {
        $mappedTransactions = [];
        foreach($transactions as $transaction) {
            $mappedTransactions[] = [
                'Id' => $transaction['id'],
                'Tipo de transação' => $transaction['transaction_type']['name'] . ' (' . $transaction['transaction_type']['id'] . ')',
                'Tipo de pagamento' => $transaction['payment_type']['name'] . ' (' . $transaction['payment_type']['id'] . ')',
                'Status de pagamento' => $transaction['payment_status']['name'] . ' (' . $transaction['payment_status']['id'] . ')',
                'Data de compra' => $transaction['purchase_date'],
                'Data de vencimento' => $transaction['due_date'],
                'Data de pagamento' => $transaction['payment_date'],
                'Total' => $transaction['amount'],
                'Parcela atual' => $transaction['current_installment'],
                'Total de parcelas' => $transaction['total_installments'],
                'Categoria primária' => $transaction['primary_category']['name'] . ' (' . $transaction['primary_category']['id'] . ')',
                'Categoria secundária' => $transaction['secondary_category']['name'] . ' (' . $transaction['secondary_category']['id'] . ')',
                'Categoria específica' => $transaction['specific_category']['name'] . ' (' . $transaction['specific_category']['id'] . ')',
                'Descrição' => $transaction['description'],
                'Observação primária' => $transaction['primary_note'],
                'Observação secundária' => $transaction['secondary_note'],
                'Média de gastos' => $transaction['spending_average'],
                'Real' => $transaction['is_real'],
                'Reconciliado' => $transaction['is_reconciled'],
                'Data de criação' => $transaction['created_at'],
                'Data de alteração' => $transaction['updated_at'],
                'Id Centro de Custo' => $transaction['cost_center_id'],
            ];
        }
        return $mappedTransactions;
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
            'executed_expense_amount' => round($totals['executed_expense_amount'], 2),
            'pending_expense_amount' => round(($totals['forecast_expense_amount'] - $totals['executed_expense_amount']), 2),
            'forecast_revenue_amount' => round($totals['forecast_revenue_amount'], 2),
            'executed_revenue_amount' => round($totals['executed_revenue_amount'], 2),
            'pending_revenue_amount' => round(($totals['forecast_revenue_amount'] - $totals['executed_revenue_amount']), 2),
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
            if ($historyTotal['transaction_type_id'] == TransactionType::EXPENSE) {
                $totals['executed_expense_amount'] += $historyTotal['amount'];
            }

            if ($historyTotal['transaction_type_id'] == TransactionType::REVENUE) {
                $totals['executed_revenue_amount'] += $historyTotal['amount'];
            }
        }

        return $totals['executed_revenue_amount'] - $totals['executed_expense_amount'];
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

    public function getGeneralBalance(array $filters): array
    {
        $originalStartDate = new \DateTime($filters['dueDateRange'][0]);
        $originalEndDate = new \DateTime($filters['dueDateRange'][1]);

        $startDate = $originalStartDate->modify('-6 months')->format('Y-m-d');
        $endDate = $originalEndDate->modify('+6 months')->format('Y-m-d');

        $monthlyTotals = $this->repository->getMonthlyAmount($startDate, $endDate);
        $initialBalance = $this->getInitialBalance($startDate);

        return $this->calculateMonthlyBalances($startDate, $endDate, $monthlyTotals, $initialBalance);
    }

    private function getInitialBalance(string $startDate): float
    {
        $historyTotals = $this->repository->getHistoryExecutedAmount($startDate);
        return $this->calcExecutedHistoryBalanceAmount($historyTotals);
    }

    private function calculateMonthlyBalances(string $startDate, string $endDate, array $monthlyTotals, float $initialBalance): array
    {
        $balances = [];
        $currentBalance = $initialBalance;

        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);

        while ($start <= $end) {
            $yearMonth = $start->format('Y-m');
            $monthlyRevenue = 0;
            $monthlyExpense = 0;

            foreach ($monthlyTotals as $total) {
                if ($total['year_month'] === $yearMonth) {
                    if ($total['transaction_type_id'] == TransactionType::REVENUE) {
                        $monthlyRevenue += $total['amount'];
                    } elseif ($total['transaction_type_id'] == TransactionType::EXPENSE) {
                        $monthlyExpense += $total['amount'];
                    }
                }
            }

            $currentBalance += ($monthlyRevenue - $monthlyExpense);

            $balances[] = [
                'year_month' => $yearMonth,
                'balance' => round($currentBalance, 2),
            ];

            $start->modify('+1 month');
        }

        return $balances;
    }
}
