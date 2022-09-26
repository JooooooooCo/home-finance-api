<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use App\Exceptions\InvalidCostCenterException;
use App\Http\Controllers\Controller;

class VerifyTenantHeader
{
    /**
     * Verify the tenant header
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $x_tenant_id = $request->header('X-Tenant-ID');

        if ($x_tenant_id > 0) {
            $controller = new Controller();
            $controller->verifyCostCenterBelongsUser($x_tenant_id);
            
            return $next($request);
        }

        throw new InvalidCostCenterException(
            "You must send the 'X-Tenant-ID' (CostCenter Id) key in the request header"
        );
    }
}
