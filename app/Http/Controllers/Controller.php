<?php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *    title="Home Finance API",
 *    version="1.0.0",
 * ),
 * @OA\SecurityScheme(
 *    securityScheme="bearerAuth",
 *    type="http",
 *    scheme="bearer"
 * ),
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Verify if user is associated to cost center
     *
     * @param  \App\CostCenter  $cost_center
     * @return bool
     */
    public function isUserOwnerCostCenter(CostCenter $cost_center)
    {
        $allCostCenters = auth()->user()->costCenters()->where('id', $cost_center->id)->get();

        return !$allCostCenters->isEmpty();
    }
}
