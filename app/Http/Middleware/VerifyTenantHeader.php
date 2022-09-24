<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use App\Exceptions\InvalidCostCenterException;

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
        if ($request->header('X-Tenant-ID') > 0) {
            return $next($request);
        }

        throw new InvalidCostCenterException;
    }
}
