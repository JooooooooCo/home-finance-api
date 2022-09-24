<?php

namespace App\Exceptions;

use Exception;

class InvalidCostCenterException extends Exception
{
    protected $message = "You must send the 'X-Tenant-ID' (CostCenter Id) key in the request header";

    public function render()
    {
        return response()->json([
            'message' => $this->message,
        ], 400);
    }
}
