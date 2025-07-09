<?php

namespace App\Http\Controllers\Settings;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Models\Settings\Setting;
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
        } catch (Throwable $throwable) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$is_updated) {
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
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }


        if (!$this->is_admin_available_for_backorder_record) {
            throw new Exception('is_admin_available_for_backorder setting not found.', 404);
        }
    }


    public function __invoke(Request $request)
    {
        $this->global_request_object = $request;

        $this->loadRequestedSetting();

        $this->toggleSettingValue();

        return response()->json([
            'message' => "is_admin_available_for_backorder setting updated successfully.",
        ], 200);
    }
}
