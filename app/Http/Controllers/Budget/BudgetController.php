<?php

namespace App\Http\Controllers\Budget;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Budget\BudgetService;

class BudgetController extends Controller
{
    protected $service;

    public function __construct(BudgetService $service)
    {
        $this->service = $service;
    }

    public function create(Request $request)
    {
        // TODO: Validar se passou 100% como no frontend
        $data = $request->all();
        $budget = $this->service->create($data);
        return $this->sendResponse($budget, 'Success, entity created');
    }

    public function get(Request $request)
    {
        $filters = $request->query();
        $budget = $this->service->get($filters);
        return $this->sendResponse($budget, 'entity details');
    }

    public function replaceCategories(Request $request)
    {
        $budgetId = $request->route('id');
        $data = $request->all();
        $budget = $this->service->replaceCategories($budgetId, $data);
        return $this->sendResponse($budget, 'Success, entity updated');
    }
}
