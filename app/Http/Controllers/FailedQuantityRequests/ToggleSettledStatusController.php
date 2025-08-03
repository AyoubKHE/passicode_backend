<?php

namespace App\Http\Controllers\FailedQuantityRequests;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Products\FailedQuantityRequest;

class ToggleSettledStatusController extends Controller
{
    private Request $global_request_object;
    private FailedQuantityRequest|null $failed_quantity_request;
    private array $sent_inputs;


    private function toggleSettledStatus()
    {
        $this->failed_quantity_request->status = "settled";
        $this->failed_quantity_request->settled_at = now();
        try {
            $is_updated = $this->failed_quantity_request->save();

            if (!$is_updated) {
                throw new Exception(
                    "- .",
                    500
                );
            }
        } catch (Throwable $th) {

            Log::channel('toggle_settled_status_errors')->error(
                "\n\n" .
                "Description: Failed to update failed quantity request status in database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Failed Quantity Request ID: " . $this->global_request_object->failed_quantity_request_id . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }
    }


    private function loadRequestedFailedQuantityRequest()
    {
        try {

            $this->failed_quantity_request = FailedQuantityRequest::where(
                "id",
                $this->global_request_object->failed_quantity_request_id
            )->first();
        } catch (Throwable $th) {

            Log::channel('toggle_settled_status_errors')->error(
                "\n\n" .
                "Description: Failed to get failed quantity request from database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Failed Quantity Request ID: " . $this->global_request_object->failed_quantity_request_id . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$this->failed_quantity_request) {

            Log::channel('toggle_settled_status_errors')->error(
                "\n\n" .
                "Description: failed quantity request not found.\n\n" .
                "Error message: - .\n\n" .
                "Failed Quantity Request ID: " . $this->global_request_object->failed_quantity_request_id . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'failed quantity request not found.',
                404
            );
        }

        if (!$this->failed_quantity_request->status === "settled") {

            Log::channel('toggle_settled_status_errors')->error(
                "\n\n" .
                "Description: failed quantity request already settled.\n\n" .
                "Error message: - .\n\n" .
                "Failed Quantity Request ID: " . $this->global_request_object->failed_quantity_request_id . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'failed quantity request already settled.',
                422
            );
        }
    }


    public function __invoke(Request $request)
    {
        $this->global_request_object = $request;

        $this->loadRequestedFailedQuantityRequest();

        $this->toggleSettledStatus();

        return response()->json([
            'message' => "Failed Quantity Request's status updated successfully.",
        ], 200);
    }
}
