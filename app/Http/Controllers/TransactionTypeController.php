<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\TransactionTypeService;

class TransactionTypeController extends Controller
{
    protected $service;

    public function __construct(TransactionTypeService $service)
    {
        $this->service = $service;
    }

    public function list()
    {
        $transactionTypes = $this->service->list();
        return $this->sendResponse($transactionTypes, 'entities collection');
    }
}
