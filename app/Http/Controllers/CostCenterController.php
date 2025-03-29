<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CostCenterService;
use Illuminate\Support\Facades\Auth;

class CostCenterController extends Controller
{
    protected $service;

    public function __construct(CostCenterService $service)
    {
        $this->service = $service;
    }

    public function list()
    {
        $costCenters = $this->service->list(Auth::user());
        return $this->sendResponse($costCenters, 'Cost centers collection');
    }

    public function create(Request $request)
    {
        $data = $request->validate(['name' => 'required|max:200']);
        $costCenter = $this->service->create(Auth::user(), $data);
        return $this->sendResponse($costCenter, 'Success, cost center created');
    }

    public function get(Request $request)
    {
        $id = $request->route('id');
        $costCenter = $this->service->get(Auth::user(), $id);
        return $this->sendResponse($costCenter, 'Cost center details');
    }

    public function update(Request $request)
    {
        $id = $request->route('id');
        $data = $request->validate(['name' => 'required|max:200']);
        $costCenter = $this->service->update(Auth::user(), $id, $data);
        return $this->sendResponse($costCenter, 'Success, cost center updated');
    }

    public function delete(Request $request)
    {
        $id = $request->route('id');
        $this->service->delete(Auth::user(), $id);
        return $this->sendResponse([], 'Success, cost center deleted');
    }
}
