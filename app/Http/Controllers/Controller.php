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
     * Success response method.
     *
     * @return \Illuminate\Http\Response
     */
    protected function sendResponse($result, $message = 'Success')
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

    /**
     * Error response method.
     *
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Verify if user is associated to cost center
     *
     * @param  \App\CostCenter  $cost_center
     * @return bool
     */
    protected function isUserOwnerCostCenter(CostCenter $cost_center)
    {
        $allCostCenters = auth()->user()->costCenters()->where('id', $cost_center->id)->get();

        return !$allCostCenters->isEmpty();
    }

    /**
     * Return user friendly error messages
     *
     * @param  string $error_message
     * @return string
     */
    private function handleErrorMessage(string $error_message)
    {
        $error_message_return = 'Internal error.';

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
