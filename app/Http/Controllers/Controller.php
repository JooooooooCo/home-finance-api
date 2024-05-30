<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Exceptions\InvalidCostCenterException;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function sendResponse($result = [], $message = 'Success')
    {
    	$response = [
            'error' => false,
            'message' => $message,
        ];

        if (!isset($result['data'])) {
            $response['data'] = $result;
        } else {
            $response = $response + $result;
        }

        return response($response, 200);
    }

    protected function sendError($error = 'Internal error', $code = 404, $errorMessages = [])
    {
    	$response = [
            'error' => true,
            'message' => $this->handleErrorMessage($error),
        ];

        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }

        return response($response, $code);
    }

    public function verifyCostCenterBelongsUser(string $cost_center_id)
    {
        $costCenters = auth()->user()->costCenters;

        foreach ($costCenters as $value) {
            if ($cost_center_id == $value->id) {
                return;
            }
        }

        throw new InvalidCostCenterException("Object not found");
    }

    private function handleErrorMessage(string $error_message)
    {
        $error_message_return = (!empty($error_message)) ? $error_message : 'Internal error.';

        $foreign_key_error = 'foreign key constraint fails';
        $foreign_key_pos = strpos($error_message, $foreign_key_error);

        if ($foreign_key_pos) {
            $table_name_start_pos = strpos($error_message, '`.`', $foreign_key_pos) + 2;
            $table_name_end_pos = strpos($error_message, ',', $table_name_start_pos);
            $table_name_length = $table_name_end_pos - $table_name_start_pos;

            $error_message_return .= ' Cannot delete or update: this resource is used by ';
            $error_message_return .= substr($error_message, $table_name_start_pos, $table_name_length);
            $error_message_return .= '.';
        }

        return $error_message_return;
    }
}
