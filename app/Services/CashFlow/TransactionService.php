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

        if (!isset($data['transactionType']) || empty(trim($data['transactionType']))) {
            throw new Exception("Transaction type is required", 422);
        }

        $allowedTypes = [TransactionType::EXPENSE->value, TransactionType::INCOME->value];
        if (!in_array($data['transactionType'], $allowedTypes)) {
            throw new Exception("Invalid transaction type", 422);
        }

        $paymentTypes = $this->repository->getPaymentTypes();
        $classifications = $this->repository->getClassifications();
        $categories = $this->repository->getCategoriesByType($data['transactionType']);
        $categoryIds = array_column($categories, 'id');
        $subCategories = $this->repository->getSubCategoriesByCategoryIds($categoryIds);

        $prompt = $this->buildAIPrompt($paymentTypes, $classifications, $categories, $subCategories);

        $aiResponse = $this->callOpenAI($prompt, $data['description']);
        $suggestedTransaction = $this->parseAIResponse($aiResponse, $data['description']);
        $suggestedTransaction = $this->addNamesToTransaction($suggestedTransaction, $classifications, $categories, $subCategories);

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

    private function buildAIPrompt(array $paymentTypes, array $classifications, array $categories, array $subCategories): string
    {
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
        Sua tarefa é analisar a mensagem fornecida e **extrair o máximo de informações possíveis** para preencher o objeto JSON.

        ---

        **REGRAS INVIOLÁVEIS**
        * **SAÍDA:** Retorne **APENAS** um objeto JSON válido. Não inclua texto explicativo, saudações ou código adicional.
        * **Omita o campo do JSON** se o valor for ambíguo ou não puder ser determinado. Retorno um objeto vazio, se todos os campos forem omitidos
        * **NÃO INVENTE DADOS FALSOS** se não conseguir inferir da mensagem fornecida.

        ---

        **INSTRUÇÕES DE EXTRAÇÃO E FORMATO:**
        * **amount, total_installments: Se existir somente um valor numérico na descrição, use-o como o valor de `amount`. Se houver mais de um valor numérico, do tipo inteiro, identifique qual deles é o `amount` e qual é o `total_installments` (considerando que as parcelas são mensais).
        * **description:** A descrição deve ser o **texto original, LIMPO de quaisquer informações que foram extraídas para outros campos** (como valores e parcelas). O objetivo é que sobre apenas o **que foi comprado/pago**.
        * **classification_id:** Com base na descrição da transação, escolha a classificação mais adequada entre as opções disponíveis (use somente o ID): {$classificationsList}
        * **category_id e sub_category_id:** Com base na descrição da transação, escolha a categoria e subcategoria mais adequadas entre as opções disponíveis (use somente os IDs). As subcategorias estão listadas entre parenteses após suas respectivas categorias: {$unifiedCategoriesList}

        ---

        **ESTRUTURA JSON ESPERADA (OMITA CAMPOS NÃO EXTRAÍDOS):**
        ```json
        {
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

    private function callOpenAI(string $prompt, $description): string
    {
        $client = new \GuzzleHttp\Client();
        // $start = microtime(true);
        $response = $client->post('https://api.openai.com/v1/responses', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                // 'model' => 'gpt-5-nano',
                // 'reasoning' => [ 'effort' => 'minimal' ],
                'model' => 'gpt-4.1-mini',
                'text' => (object) [
                    'format' => (object) [
                        'type' => 'json_object'
                    ]
                ],
                'reasoning' => (object) [],
                'tools' => [],
                'temperature' => 1,
                'max_output_tokens' => 2048,
                'top_p' => 1,
                'store' => false,
                'input' => [
                    (object) [
                        'role' => 'system',
                        'content' => [
                            (object) [
                                'type' => 'input_text',
                                'text' => $prompt
                            ]
                        ],

                    ],
                    (object) [
                        'role' => 'user',
                        'content' => [
                            (object) [
                                'type' => 'input_text',
                                'text' => $description
                            ]
                        ],

                    ],
                ]
            ],
        ]);
        // $end = microtime(true);
        // $durationApiResponse = number_format(($end - $start), 4);
        // print_r($durationApiResponse);
        
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

    private function parseAIResponse(string $aiResponse, string $description): array
    {
        $parsed = json_decode($aiResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid AI response format", 422);
        }

        $parsed['amount'] = $parsed['amount'] ?? null;
        $parsed['description'] = $parsed['description'] ?? $description;
        $parsed['classification_id'] = $parsed['classification_id'] ?? null;
        $parsed['category_id'] = $parsed['category_id'] ?? null;
        $parsed['sub_category_id'] = $parsed['sub_category_id'] ?? null;
        $parsed['current_installment'] = 1;
        $parsed['total_installments'] = $parsed['total_installments'] ?? 1;
        $parsed['status'] = null;
        $parsed['is_real'] = 1;
        $parsed['is_reconciled'] = 0;
        $parsed['primary_note'] = '';
        $parsed['secondary_note'] = '';
        $parsed['spending_average'] = '';

        return $parsed;
    }

    private function addNamesToTransaction(array $transaction, array $classifications, array $categories, array $subCategories): array
    {
        $transaction['classification_name'] = collect($classifications)->firstWhere('id', $transaction['classification_id'])['name'] ?? '';
        $transaction['category_name'] = collect($categories)->firstWhere('id', $transaction['category_id'])['name'] ?? '';
        $transaction['sub_category_name'] = collect($subCategories)->firstWhere('id', $transaction['sub_category_id'])['name'] ?? '';

        return $transaction;
    }
}
