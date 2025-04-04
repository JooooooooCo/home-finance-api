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

    public function list()
    {
        $transactions = $this->service->list();
        return $this->sendResponse($transactions, 'entities collection');
    }

    public function create(Request $request)
    {
        $data = $request->all();
        $transaction = $this->service->create($data);
        return $this->sendResponse($transaction, 'Success, entity created');
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
