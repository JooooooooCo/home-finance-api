<?php

namespace App\Http\Controllers\CashFlow;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\CashFlow\TransactionService;

class TransactionController extends Controller
{
    protected $service;

    public function __construct(TransactionService $service)
    {
        $this->service = $service;
    }

    public function list(Request $request)
    {
        $filters = $request->query();
        $transactions = $this->service->list($filters);
        return $this->sendResponse($transactions, 'entities collection');
    }

    public function getTotalSummary(Request $request)
    {
        $filters = $request->query();
        $data = $this->service->list($filters);
        unset($data['transactions']);
        return $this->sendResponse($data, 'entities collection');
    }

    public function getGeneralBalance(Request $request)
    {
        $filters = $request->query();
        $balances = $this->service->getGeneralBalance($filters);
        return $this->sendResponse($balances, 'General balance historical data');
    }

    public function export(Request $request)
    {
        ini_set('memory_limit', '512M');
        $filters = $request->query();
        return $this->service->export($filters);
    }

    public function create(Request $request)
    {
        $data = $request->all();
        $transaction = $this->service->create($data);
        return $this->sendResponse($transaction, 'Success, entity created');
    }
    
    public function createBatch(Request $request)
    {
      $data = $request->all();
      $transactions = $this->service->createBatch($data);    
      return $this->sendResponse($transactions, 'Success, multiple entities created');
    }

    public function get(Request $request)
    {
        $id = $request->route('id');
        $transaction = $this->service->get($id);
        return $this->sendResponse($transaction, 'entity details');
    }

    public function update(Request $request)
    {
        $id = $request->route('id');
        $data = $request->all();
        $transaction = $this->service->update($id, $data);
        return $this->sendResponse($transaction, 'Success, entity updated');
    }

    public function delete(Request $request)
    {
        $id = $request->route('id');
        $this->service->delete($id);
        return $this->sendResponse([], 'Success, entity deleted');
    }
}
