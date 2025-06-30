<?php

namespace App\Http\Controllers\FailedQuantityRequests;

use Exception;
use Throwable;
use Illuminate\Http\Request;
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


    private function loadRequestedFailedQuantityRequest()
    {
        try {

            $this->failed_quantity_request = FailedQuantityRequest::where(
                "id",
                $this->global_request_object->failed_quantity_request_id
            )->first();
        } catch (Throwable $th) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$this->failed_quantity_request) {
            throw new Exception('failed quantity request not found.', 404);
        }

        if (!$this->failed_quantity_request->status === "settled") {
            throw new Exception('failed quantity request already settled.', 422);
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
