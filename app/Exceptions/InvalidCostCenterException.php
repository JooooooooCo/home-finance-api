<?php

namespace App\Exceptions;

use Exception;

class InvalidCostCenterException extends Exception
{
    protected $message;

    public function __construct(string $message = "Invalid 'X-Tenant-ID' (CostCenter Id)") {
        $this->message = $message;
    }

    public function render()
    {
        return response()->json([
            'message' => $this->message,
        ], 400);
    }
}
