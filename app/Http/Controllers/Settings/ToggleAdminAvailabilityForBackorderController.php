<?php

namespace App\Http\Controllers\Settings;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Models\Settings\Setting;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class ToggleAdminAvailabilityForBackorderController extends Controller
{
    private Request $global_request_object;
    private Setting|null $is_admin_available_for_backorder_record;

    private function toggleSettingValue()
    {
        $this->is_admin_available_for_backorder_record->value = $this->is_admin_available_for_backorder_record->value === "true" ? "false" : "true";
        $this->is_admin_available_for_backorder_record->updated_at = now();
        try {
            $is_updated = $this->is_admin_available_for_backorder_record->save();

            if (!$is_updated) {
                throw new Exception(
                    "- .",
                    500
                );
            }
        } catch (Throwable $th) {
            try {
                Log::channel('toggle_admin_availability_for_backorder_errors')->error(
                    "\n\n" .
                    "Description: failed to update << is_admin_available_for_backorder >> value in database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                    "old is_admin_available_for_backorder: " . $this->is_admin_available_for_backorder_record->value . "\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );
            } catch (Throwable $th) {
                //throw $th;
            }

            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }
    }


    private function loadRequestedSetting()
    {
        try {

            $this->is_admin_available_for_backorder_record = Setting::where(
                "key",
                "is_admin_available_for_backorder"
            )
                ->first();
        } catch (Throwable $th) {
            try {
                Log::channel('toggle_admin_availability_for_backorder_errors')->error(
                    "\n\n" .
                    "Description: Failed to get << is_admin_available_for_backorder >> information from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );
            } catch (Throwable $th) {
                //throw $th;
            }

            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }


        if (!$this->is_admin_available_for_backorder_record) {
            try {
                Log::channel('toggle_admin_availability_for_backorder_errors')->error(
                    "\n\n" .
                    "Description: << is_admin_available_for_backorder >> setting not found.\n\n" .
                    "Error message: - .\n\n" .
                    "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );
            } catch (Throwable $th) {
                //throw $th;
            }

            throw new Exception(
                'is_admin_available_for_backorder setting not found.',
                404
            );
        }
    }

    private function logRequest()
    {
        try {
            Log::channel('toggle_admin_availability_for_backorder_requests')->info(
                "\n\n" .
                "Description: is_admin_available_for_backorder setting updated successfully.\n\n" .
                "New Value: " . $this->is_admin_available_for_backorder_record->value . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );
        } catch (Throwable $th) {
            //throw $th;
        }

    }

    public function __invoke(Request $request)
    {
        $this->global_request_object = $request;

        $this->loadRequestedSetting();

        $this->toggleSettingValue();

        $this->logRequest();

        return response()->json([
            'message' => "is_admin_available_for_backorder setting updated successfully.",
        ], 200);
    }
}
