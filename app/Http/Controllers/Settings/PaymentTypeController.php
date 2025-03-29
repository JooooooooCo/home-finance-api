<?php

namespace App\Http\Controllers\Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Settings\PaymentTypeService;

class PaymentTypeController extends Controller
{
    protected $service;

    public function __construct(PaymentTypeService $service)
    {
        $this->service = $service;
    }

    public function list()
    {
        $paymentTypes = $this->service->list();
        return $this->sendResponse($paymentTypes, 'entitiess collection');
    }

    public function create(Request $request)
    {
        $data = $request->validate(['name' => 'required|max:200']);
        $paymentType = $this->service->create($data);
        return $this->sendResponse($paymentType, 'Success, entity created');
    }

    public function get(Request $request)
    {
        $id = $request->route('id');
        $paymentType = $this->service->get($id);
        return $this->sendResponse($paymentType, 'entity details');
    }

    public function update(Request $request)
    {
        $id = $request->route('id');
        $data = $request->validate(['name' => 'required|max:200']);
        $paymentType = $this->service->update($id, $data);
        return $this->sendResponse($paymentType, 'Success, entity updated');
    }

    public function delete(Request $request)
    {
        $id = $request->route('id');
        $this->service->delete($id);
        return $this->sendResponse([], 'Success, entity deleted');
    }
}
