<?php

namespace App\Services\CashFlow;

use App\Enums\TransactionType;
use App\Enums\PaymentStatus;
use Exception;
use App\Models\CashFlow\Transaction;
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
        foreach ($transactions as $transaction) {
            $mappedTransactions[] = [
                'Id' => $transaction['id'],
                'Tipo de transação' => $transaction['type'],
                'Tipo de pagamento' => $transaction['payment_type']['name'] . ' (' . $transaction['payment_type']['id'] . ')',
                'Status de pagamento' => $transaction['status'],
                'Data de compra' => $transaction['purchase_date'],
                'Data de vencimento' => $transaction['due_date'],
                'Data de pagamento' => $transaction['payment_date'],
                'Total' => $transaction['amount'],
                'Parcela atual' => $transaction['current_installment'],
                'Total de parcelas' => $transaction['total_installments'],
                'Classificação' => $transaction['classification']['name'] . ' (' . $transaction['classification']['id'] . ')',
                'Categoria' => $transaction['category']['name'] . ' (' . $transaction['category']['id'] . ')',
                'Subcategoria' => $transaction['sub_category']['name'] . ' (' . $transaction['sub_category']['id'] . ')',
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
        $forecastBalanceAmount = ($executedHistoryBalanceAmount + $totals['forecast_income_amount']) - $totals['forecast_expense_amount'];
        $executedBalanceAmount = ($executedHistoryBalanceAmount + $totals['executed_income_amount']) - $totals['executed_expense_amount'];

        return [
            'executed_history_balance_amount' => round($executedHistoryBalanceAmount, 2),
            'forecast_balance_amount' => round($forecastBalanceAmount, 2),
            'executed_balance_amount' => round($executedBalanceAmount, 2),
            'forecast_expense_amount' => round($totals['forecast_expense_amount'], 2),
            'executed_expense_amount' => round($totals['executed_expense_amount'], 2),
            'pending_expense_amount' => round(($totals['forecast_expense_amount'] - $totals['executed_expense_amount']), 2),
            'forecast_income_amount' => round($totals['forecast_income_amount'], 2),
            'executed_income_amount' => round($totals['executed_income_amount'], 2),
            'pending_income_amount' => round(($totals['forecast_income_amount'] - $totals['executed_income_amount']), 2),
        ];
    }

    private function calcFilteredTotals($transactions)
    {
        $totals = [
            'forecast_expense_amount' => 0,
            'executed_expense_amount' => 0,
            'forecast_income_amount' => 0,
            'executed_income_amount' => 0,
        ];

        foreach ($transactions as $transaction) {
            if ($transaction['type'] == TransactionType::EXPENSE->value) {
                $totals['forecast_expense_amount'] += $transaction['amount'];

                if ($transaction['status'] == PaymentStatus::PAID->value) {
                    $totals['executed_expense_amount'] += $transaction['amount'];
                }
            }

            if ($transaction['type'] == TransactionType::INCOME->value) {
                $totals['forecast_income_amount'] += $transaction['amount'];

                if ($transaction['status'] == PaymentStatus::PAID->value) {
                    $totals['executed_income_amount'] += $transaction['amount'];
                }
            }
        }

        return $totals;
    }

    private function calcExecutedHistoryBalanceAmount($historyTotals)
    {
        $totals = [
            'executed_expense_amount' => 0,
            'executed_income_amount' => 0,
        ];

        foreach ($historyTotals as $historyTotal) {
            if ($historyTotal['type'] == TransactionType::EXPENSE->value) {
                $totals['executed_expense_amount'] += $historyTotal['amount'];
            }

            if ($historyTotal['type'] == TransactionType::INCOME->value) {
                $totals['executed_income_amount'] += $historyTotal['amount'];
            }
        }

        return $totals['executed_income_amount'] - $totals['executed_expense_amount'];
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

    public function aiSuggest(array $data): array
    {
        if (!isset($data['description']) || empty(trim($data['description']))) {
            throw new Exception("Description is required", 422);
        }

        $paymentTypes = $this->repository->getPaymentTypes();
        $classifications = $this->repository->getClassifications();
        $categories = $this->repository->getCategories();
        $subCategories = $this->repository->getSubCategories();

        $prompt = $this->buildAIPrompt($data['description'], $paymentTypes, $classifications, $categories, $subCategories);

        $aiResponse = $this->callOpenAI($prompt);
        $suggestedTransaction = $this->parseAIResponse($aiResponse, $paymentTypes);
        $suggestedTransaction = $this->addNamesToTransaction($suggestedTransaction, $paymentTypes, $classifications, $categories, $subCategories);

        return $suggestedTransaction;
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

    public function getPerPaymentTypeBalance(array $filters): array
    {
        $originalStartDate = new \DateTime($filters['dueDateRange'][0]);
        $originalEndDate = new \DateTime($filters['dueDateRange'][1]);

        $startDate = $originalStartDate->modify('-6 months')->format('Y-m-d');
        $endDate = $originalEndDate->modify('+6 months')->format('Y-m-d');
        $paymentTypeId = (int) $filters['paymentTypeId'];

        $monthlyTotals = $this->repository->getMonthlyAmountByPaymentType($startDate, $endDate, $paymentTypeId);
        $initialBalance = $this->getInitialBalanceByPaymentType($startDate, $paymentTypeId);

        return $this->calculateMonthlyBalances($startDate, $endDate, $monthlyTotals, $initialBalance);
    }

    private function getInitialBalance(string $startDate): float
    {
        $historyTotals = $this->repository->getHistoryExecutedAmount($startDate);
        return $this->calcExecutedHistoryBalanceAmount($historyTotals);
    }

    private function getInitialBalanceByPaymentType(string $startDate, int $paymentTypeId): float
    {
        $historyTotals = $this->repository->getHistoryExecutedAmountByPaymentType($startDate, $paymentTypeId);
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
            $monthlyIncome = 0;
            $monthlyExpense = 0;

            foreach ($monthlyTotals as $total) {
                if ($total['year_month'] === $yearMonth) {
                    if ($total['type'] == TransactionType::INCOME->value) {
                        $monthlyIncome += $total['amount'];
                    } elseif ($total['type'] == TransactionType::EXPENSE->value) {
                        $monthlyExpense += $total['amount'];
                    }
                }
            }

            $currentBalance += ($monthlyIncome - $monthlyExpense);

            $balances[] = [
                'year_month' => $yearMonth,
                'balance' => round($currentBalance, 2),
            ];

            $start->modify('+1 month');
        }

        return $balances;
    }

    private function buildAIPrompt(string $description, array $paymentTypes, array $classifications, array $categories, array $subCategories): string
    {
        $typeExpense = TransactionType::EXPENSE->value;
        $typeIncome = TransactionType::INCOME->value;
        $statusPaid = PaymentStatus::PAID->value;
        $statusPending = PaymentStatus::PENDING->value;
        $currentDate = date('Y-m-d');
        $currentYear = date('Y');
        $paymentTypesList = collect($paymentTypes)->map(fn($pt) => "{$pt['id']}: {$pt['name']}")->join(', ');
        $classificationsList = collect($classifications)->map(fn($c) => "{$c['id']}: {$c['name']}")->join(', ');
        $unifiedCategoriesList = '[ ';
        foreach ($categories as $category) {
            $unifiedCategoriesList .= "{$category['id']}: {$category['name']} (sub-categorias: ";
            $filteredSubCategories = collect($subCategories)->where('category_id', $category['id']);
            foreach ($filteredSubCategories as $subCategory) {
                $unifiedCategoriesList .= " {$subCategory['id']}: {$subCategory['name']}";
            }
            $unifiedCategoriesList .= "), ";
        }
        $unifiedCategoriesList .= ' ]';

        return "Você é um especialista em análise de transações financeiras.
        Sua tarefa é analisar a descrição fornecida e **extrair o máximo de informações possíveis** para preencher o objeto JSON.

        **Descrição da Transação Original:** '{$description}'
        
        ---
        
        **REGRAS DE EXTRAÇÃO E FORMATO:**
        * **SAÍDA:** Retorne **APENAS** um objeto JSON válido. Não inclua texto explicativo, saudações ou código adicional.
        * **VALORES PADRÃO:** **NÃO** utilize valores padrão (como a data atual ou 0.00). 
        * **Omita o campo do JSON** se o valor for ambíguo ou não puder ser determinado.
        * **type:** Defina o tipo da transação entre {$typeExpense} OU {$typeIncome}. Se não conseguir, considere como {$typeExpense}
        * **payment_type_id:** Defina o ID dentro da lista fornecida: {$paymentTypesList}
        * **status:** Defina o status entre {$statusPaid} OU {$statusPending}. Se não conseguir, considere como {$statusPaid}
        * **purchase_date, due_date, payment_date:** Extraia as datas no formato `YYYY-MM-DD`. Se houver apenas uma data, assuma que é a `purchase_date`. Se houver mais de uma data, considere a mais antiga como `purchase_date`, a seguinte como `due_date` e mais recente como `payment_date`. Se o ano não estiver explícito, considere como {$currentYear}. Se não conseguir inferir a data de compra, considere como {$currentDate}.
        * **amount, total_installments: Se existir somente um valor numérico na descrição, use-o como o valor de `amount`. Se houver mais de um valor numérico, do tipo inteiro, identifique qual deles é o `amount` e qual é o `total_installments` (considerando que as parcelas são mensais).
        * **description:** A descrição deve ser o **texto original, LIMPO de quaisquer informações que foram extraídas para outros campos** (como datas, valores, ou menções diretas a tipos/status de pagamento). O objetivo é que sobre apenas o **que foi comprado/pago**.
        * **classification_id:** Com base na descrição da transação, escolha a classificação mais adequada entre as opções disponíveis (use somente o ID): {$classificationsList}
        * **category_id e sub_category_id:** Com base na descrição da transação, escolha a categoria e subcategoria mais adequadas entre as opções disponíveis (use somente os IDs). As subcategorias estão listadas entre parenteses após suas respectivas categorias: {$unifiedCategoriesList}
        
        ---

        **ESTRUTURA JSON ESPERADA (OMITA CAMPOS NÃO EXTRAÍDOS):**
        ```json
        {
          \"type\": \"string\",
          \"payment_type_id\": id,
          \"status\": \"string\",
          \"purchase_date\": \"YYYY-MM-DD\",
          \"due_date\": \"YYYY-MM-DD\",
          \"payment_date\": \"YYYY-MM-DD\",
          \"amount\": 0.00,
          \"total_installments\": 1,
          \"classification_id\": id,
          \"category_id\": id,
          \"sub_category_id\": id,
          \"description\": \"string\"
        }
        ```
        
        **NÃO INCLUA NADA ALÉM DO JSON SOLICITADO.**";
    }

    private function callOpenAI(string $prompt): string
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->post('https://api.openai.com/v1/responses', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-5-nano',
                'reasoning' => [ 'effort' => 'minimal' ],
                'input' => $prompt
            ],
        ]);

        $result = '';
        $data = json_decode($response->getBody(), true);
        foreach ($data['output'] as $output) {
            if (isset($output['content']) && !empty($output['content'])) {
                foreach ($output['content'] as $content) {
                    if (isset($content['type']) && $content['type'] === 'output_text' && isset($content['text'])) {
                        $result = $content['text'];
                    }
                }
            }
        }
        return $result;
    }

    private function parseAIResponse(string $aiResponse, array $paymentTypes): array
    {
        $parsed = json_decode($aiResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid AI response format", 422);
        }

        $requiredFields = ['classification_id', 'category_id', 'sub_category_id', 'description'];
        foreach ($requiredFields as $field) {
            if (!isset($parsed[$field])) {
                throw new Exception("Please provide more details", 422);
            }
        }

        $parsed['type'] = $parsed['type'] ?? TransactionType::EXPENSE->value;
        $parsed['amount'] = $parsed['amount'] ?? 0;
        $parsed['payment_type_id'] = $parsed['payment_type_id'] ?? $paymentTypes[0]['id'];
        $parsed['purchase_date'] = $parsed['purchase_date'] ?? date('Y-m-d');
        $parsed['current_installment'] = 1;
        $parsed['total_installments'] = $parsed['total_installments'] ?? 1;
        $parsed['status'] = $parsed['status'] ?? PaymentStatus::PENDING->value;
        $parsed['is_real'] = 1;
        $parsed['is_reconciled'] = 0;
        $parsed['primary_note'] = '';
        $parsed['secondary_note'] = '';
        $parsed['spending_average'] = '';

        return $parsed;
    }

    private function addNamesToTransaction(array $transaction, array $paymentTypes, array $classifications, array $categories, array $subCategories): array
    {
        $transaction['payment_type_name'] = collect($paymentTypes)->firstWhere('id', $transaction['payment_type_id'])['name'] ?? '';
        $transaction['classification_name'] = collect($classifications)->firstWhere('id', $transaction['classification_id'])['name'] ?? '';
        $transaction['category_name'] = collect($categories)->firstWhere('id', $transaction['category_id'])['name'] ?? '';
        $transaction['sub_category_name'] = collect($subCategories)->firstWhere('id', $transaction['sub_category_id'])['name'] ?? '';

        return $transaction;
    }
}
