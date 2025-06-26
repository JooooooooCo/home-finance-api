<?php

namespace App\Http\Controllers\Budget;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Budget\BudgetResultsService;

class BudgetResultsController extends Controller
{
    protected $service;

    public function __construct(BudgetResultsService $service)
    {
        $this->service = $service;
    }

    public function getResults(Request $request)
    {
        $filters = $request->query();
        $budget = $this->service->get($filters);
        return $this->sendResponse($budget, 'entity details');
    }
}
