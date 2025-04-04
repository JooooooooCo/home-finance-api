<?php

namespace App\Http\Controllers\Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Settings\PaymentStatusTypeService;

class PaymentStatusTypeController extends Controller
{
    protected $service;

    public function __construct(PaymentStatusTypeService $service)
    {
        $this->service = $service;
    }

    public function list()
    {
        $paymentStatusTypes = $this->service->list();
        return $this->sendResponse($paymentStatusTypes, 'entities collection');
    }

    public function create(Request $request)
    {
        $data = $request->validate(['name' => 'required|max:200']);
        $paymentStatusType = $this->service->create($data);
        return $this->sendResponse($paymentStatusType, 'Success, entity created');
    }

    public function get(Request $request)
    {
        $id = $request->route('id');
        $paymentStatusType = $this->service->get($id);
        return $this->sendResponse($paymentStatusType, 'entity details');
    }

    public function update(Request $request)
    {
        $id = $request->route('id');
        $data = $request->validate(['name' => 'required|max:200']);
        $paymentStatusType = $this->service->update($id, $data);
        return $this->sendResponse($paymentStatusType, 'Success, entity updated');
    }

    public function delete(Request $request)
    {
        $id = $request->route('id');
        $this->service->delete($id);
        return $this->sendResponse([], 'Success, entity deleted');
    }
}
