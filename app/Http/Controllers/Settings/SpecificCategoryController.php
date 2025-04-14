<?php

namespace App\Http\Controllers\Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Settings\SpecificCategoryService;

class SpecificCategoryController extends Controller
{
    protected $service;

    public function __construct(SpecificCategoryService $service)
    {
        $this->service = $service;
    }

    public function list(Request $request)
    {
        $secondaryCategoryId = $request->get('secondary-category-id');
        $itens = $this->service->list((int) $secondaryCategoryId);
        return $this->sendResponse($itens, 'entities collection');
    }

    public function create(Request $request)
    {
        $data = $request->validate(['name' => 'required|max:200', 'secondary-category-id' => 'required|max:200']);
        $item = $this->service->create($data);
        return $this->sendResponse($item, 'Success, entity created');
    }

    public function get(Request $request)
    {
        $id = $request->route('id');
        $item = $this->service->get($id);
        return $this->sendResponse($item, 'entity details');
    }

    public function update(Request $request)
    {
        $id = $request->route('id');
        $data = $request->validate(['name' => 'required|max:200', 'secondary-category-id' => 'required|max:200']);
        $item = $this->service->update($id, $data);
        return $this->sendResponse($item, 'Success, entity updated');
    }

    public function delete(Request $request)
    {
        $id = $request->route('id');
        $this->service->delete($id);
        return $this->sendResponse([], 'Success, entity deleted');
    }
}
