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
    /**
      * @OA\Get(
      * path="/api/cost-center",
      * summary="Get list of Cost Centers",
      * tags={"Cost Centers"},
      * security={{"bearerAuth":{}}},
      *      @OA\Response(
      *          response=200,
      *          description="Return list of Cost Centers",
      *          @OA\JsonContent(
      *               type="array",
      *               @OA\Items(
      *                    type="object",
      *                    @OA\Property(property="id", type="integer"),
      *                    @OA\Property(property="name", type="string"),
      *                    @OA\Property(property="created_at", type="string"),
      *                    @OA\Property(property="updated_at", type="string"),
      *               ),
      *         ),
      *      ),
      *      @OA\Response(
      *          response=401,
      *          description="Unauthorized",
      *          @OA\JsonContent(
      *               type="object",
      *               @OA\Property(property="message", type="string", example="Unauthenticated."),
      *          ),
      *      ),
      *      @OA\Response(response=400, description="Bad request"),
      *      @OA\Response(response=404, description="Resource Not Found"),
      * )
      *
      * @return \Illuminate\Http\Response
      */
    public function index()
    {
        $costCenters = CostCenterResource::collection(
            auth()->user()->costCenters
        );

        return $this->sendResponse($costCenters, 'Cost centers collection');
    }

    /**
      * @OA\Post(
      * path="/api/cost-center",
      * tags={"Cost Centers"},
      * summary="Store a new Cost Center",
      * security={{"bearerAuth":{}}},
      *      @OA\RequestBody(
      *          required=true,
      *          @OA\JsonContent(
      *             type="object",
      *             required={"name"},
      *             @OA\Property(property="name", type="string", maxLength=200),
      *          ),
      *      ),
      *      @OA\Response(
      *          response=200,
      *          description="Store a newly cost center in storage",
      *          @OA\JsonContent(
      *               type="object",
      *               @OA\Property(property="id", type="integer"),
      *               @OA\Property(property="name", type="string"),
      *               @OA\Property(property="created_at", type="string"),
      *               @OA\Property(property="updated_at", type="string"),
      *         ),
      *      ),
      *      @OA\Response(
      *          response=401,
      *          description="Unauthorized",
      *          @OA\JsonContent(
      *               type="object",
      *               @OA\Property(property="message", type="string", example="Unauthenticated."),
      *          ),
      *      ),
      *      @OA\Response(
      *          response=500,
      *          description="Internal Server Error",
      *          @OA\JsonContent(
      *               type="object",
      *               @OA\Property(property="message", type="string", example="Internal error"),
      *          ),
      *      ),
      *      @OA\Response(response=400, description="Bad request"),
      *      @OA\Response(response=404, description="Resource Not Found"),
      * ),
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
    public function store(Request $request)
    {
        $user_id = auth()->user()->id;
        $data = $request->all();

        $validator = Validator::make($data, [
            'name' => 'required|max:200'
        ]);

        if($validator->fails()){
            return response([
                'error' => $validator->errors(),
                'message' => 'Validation Error'
            ]);
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

    /**
      * @OA\Get(
      * path="/api/cost-center/{id}",
      * summary="Get a Cost Centers",
      * tags={"Cost Centers"},
      * security={{"bearerAuth":{}}},
      *      @OA\Parameter(
      *          name="id",
      *          in="path",
      *          required=true,
      *          description="The cost center ID",
      *          @OA\Schema(type="string"),
      *      ),
      *      @OA\Response(
      *          response=200,
      *          description="Return a Cost Centers",
      *          @OA\JsonContent(
      *               type="object",
      *               @OA\Property(property="id", type="integer"),
      *               @OA\Property(property="name", type="string"),
      *               @OA\Property(property="created_at", type="string"),
      *               @OA\Property(property="updated_at", type="string"),
      *         ),
      *      ),
      *      @OA\Response(
      *          response=401,
      *          description="Unauthorized",
      *          @OA\JsonContent(
      *               type="object",
      *               @OA\Property(property="message", type="string", example="Unauthenticated."),
      *          ),
      *      ),
      *      @OA\Response(response=400, description="Bad request"),
      *      @OA\Response(response=404, description="Resource Not Found"),
      * )
      *
      * @param  \App\CostCenter  $cost_center
      * @return \Illuminate\Http\Response
      */
    public function show(CostCenter $cost_center)
    {
        if (!$this->isUserOwnerCostCenter($cost_center)) {
            return $this->sendError('Object not found', 422);
        }

        $return_data = new CostCenterResource($cost_center);

        return $this->sendResponse($return_data, 'Cost center details');
    }

    /**
      * @OA\Put(
      * path="/api/cost-center/{id}",
      * tags={"Cost Centers"},
      * summary="Update a Cost Center",
      * security={{"bearerAuth":{}}},
      *      @OA\Parameter(
      *          name="id",
      *          in="path",
      *          required=true,
      *          description="The cost center ID",
      *          @OA\Schema(type="string"),
      *      ),
      *      @OA\RequestBody(
      *          required=true,
      *          @OA\JsonContent(
      *             type="object",
      *             required={"name"},
      *             @OA\Property(property="name", type="string", maxLength=200),
      *          ),
      *      ),
      *      @OA\Response(
      *          response=200,
      *          description="Update a cost center in storage",
      *          @OA\JsonContent(
      *               type="object",
      *               @OA\Property(property="id", type="integer"),
      *               @OA\Property(property="name", type="string"),
      *               @OA\Property(property="created_at", type="string"),
      *               @OA\Property(property="updated_at", type="string"),
      *               @OA\Property(property="message", type="string"),
      *         ),
      *      ),
      *      @OA\Response(
      *          response=401,
      *          description="Unauthorized",
      *          @OA\JsonContent(
      *               type="object",
      *               @OA\Property(property="message", type="string", example="Unauthenticated."),
      *          ),
      *      ),
      *      @OA\Response(
      *          response=500,
      *          description="Internal Server Error",
      *          @OA\JsonContent(
      *               type="object",
      *               @OA\Property(property="message", type="string", example="Internal error"),
      *          ),
      *      ),
      *      @OA\Response(response=400, description="Bad request"),
      *      @OA\Response(response=404, description="Resource Not Found"),
      * ),
      *
      * @param  \Illuminate\Http\Request  $request
      * @param  \App\CostCenter  $cost_center
      * @return \Illuminate\Http\Response
      */
    public function update(Request $request, CostCenter $cost_center)
    {
        if (!$this->isUserOwnerCostCenter($cost_center)) {
            return $this->sendError('Object not found', 422);
        }

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

    /**
      * @OA\Delete(
      * path="/api/cost-center/{id}",
      * tags={"Cost Centers"},
      * summary="Delete a Cost Center",
      * security={{"bearerAuth":{}}},
      *      @OA\Parameter(
      *          name="id",
      *          in="path",
      *          required=true,
      *          description="The cost center ID",
      *          @OA\Schema(type="string"),
      *      ),
      *      @OA\Response(
      *          response=200,
      *          description="Delete a cost center in storage",
      *          @OA\JsonContent(
      *               type="object",
      *               @OA\Property(property="message", type="string", example="Cost Center deleted"),
      *         ),
      *      ),
      *      @OA\Response(
      *          response=401,
      *          description="Unauthorized",
      *          @OA\JsonContent(
      *               type="object",
      *               @OA\Property(property="message", type="string", example="Unauthenticated."),
      *          ),
      *      ),
      *      @OA\Response(
      *          response=500,
      *          description="Internal Server Error",
      *          @OA\JsonContent(
      *               type="object",
      *               @OA\Property(property="message", type="string", example="Internal error"),
      *          ),
      *      ),
      *      @OA\Response(
      *          response=422,
      *          description="Unprocessable Content",
      *          @OA\JsonContent(
      *               type="object",
      *               @OA\Property(property="message", type="string", example="Object not found"),
      *          ),
      *      ),
      *      @OA\Response(response=400, description="Bad request"),
      *      @OA\Response(response=404, description="Resource Not Found"),
      * ),
      *
      * @param  \App\CostCenter  $cost_center
      * @return \Illuminate\Http\Response
      */
    public function destroy(CostCenter $cost_center)
    {
        if (!$this->isUserOwnerCostCenter($cost_center)) {
            return $this->sendError('Object not found', 422);
        }

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
