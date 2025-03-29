<?php

namespace App\Http\Middleware;

use App\Exceptions\InvalidCostCenterException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyTenantHeader
{
    public function handle(Request $request, Closure $next)
    {
        $tenantId = (int) $request->header('X-Tenant-ID');

        if (!$tenantId || !ctype_digit((string)$tenantId) || $tenantId <= 0) {
            throw new InvalidCostCenterException(
                "Valid 'X-Tenant-ID' (CostCenter Id) header is required"
            );
        }

        if (!Auth::user()->costCenters()->where('id', $tenantId)->exists()) {
            throw new InvalidCostCenterException('X-Tenant-ID / Cost center does not belong to user');
        }

        Auth::user()->tenant_id = $tenantId;

        return $next($request);
    }
}
