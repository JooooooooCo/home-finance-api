<?php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use App\Models\CostCenterUser;
use App\Http\Controllers\Controller;
use App\Http\Resources\CostCenterResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CostCenterController extends Controller
{
    public function index()
    {
        $costCenters = CostCenterResource::collection(
            auth()->user()->costCenters
        );

        return $this->sendResponse($costCenters, 'Cost centers collection');
    }

    public function store(Request $request)
    {
        $user_id = auth()->user()->id;
        $data = $request->all();

        $validator = Validator::make($data, [
            'name' => 'required|max:200'
        ]);

        if($validator->fails()){
            return $this->sendError("Validation Error", 422, $validator->errors());
        }

        try {
            \DB::beginTransaction();

            $cost_center = CostCenter::create($data);
            $cost_center->users()->attach($user_id);

            $return_data = new CostCenterResource($cost_center);

            \DB::commit();

            return $this->sendResponse($return_data, 'Success, cost center created');
        } catch (\PDOException $e) {
            \DB::rollBack();

            return $this->sendError($e->getMessage(), 500);
        }
    }

    public function show(CostCenter $cost_center)
    {
        $this->verifyCostCenterBelongsUser($cost_center->id);

        $return_data = new CostCenterResource($cost_center);

        return $this->sendResponse($return_data, 'Cost center details');
    }

    public function update(Request $request, CostCenter $cost_center)
    {
        $this->verifyCostCenterBelongsUser($cost_center->id);

        try {
            \DB::beginTransaction();

            $cost_center->update($request->all());

            $return_data = new CostCenterResource($cost_center);

            \DB::commit();

            return $this->sendResponse($return_data, 'Success, cost center updated');
        } catch (\PDOException $e) {
            \DB::rollBack();

            return $this->sendError($e->getMessage(), 500);
        }
    }

    public function destroy(CostCenter $cost_center)
    {
        $this->verifyCostCenterBelongsUser($cost_center->id);

        try {
            \DB::beginTransaction();

            $cost_center_user = CostCenterUser::where('user_id', auth()->user()->id)
                ->where('cost_center_id', $cost_center->id);

            $cost_center_user->delete();

            $cost_center->delete();

            \DB::commit();

            return $this->sendResponse([], 'Success, cost center deleted');
        } catch (\PDOException $e) {
            \DB::rollBack();

            return $this->sendError($e->getMessage(), 500);
        }
    }
}
