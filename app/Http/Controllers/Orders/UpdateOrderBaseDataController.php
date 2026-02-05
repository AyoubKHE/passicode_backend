<?php

namespace App\Http\Controllers\Orders;

use Exception;
use Throwable;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Models\Orders\ChargilyPayment;
use App\Http\Requests\Orders\UpdateOrderBaseDataRequest;


class UpdateOrderBaseDataController extends Controller
{
    private UpdateOrderBaseDataRequest $global_request_object;
    private Order|null $requested_order;
    private Order|null $old_requested_order;
    private ChargilyPayment|null $related_chargily_payment;
    private ChargilyPayment|null $old_related_chargily_payment;
    private array $sent_inputs;

    private function updateChargilyPayment()
    {
        if (
            array_key_exists('amount', $this->sent_inputs) ||
            array_key_exists('payment_status', $this->sent_inputs)
        ) {

            if (array_key_exists('amount', $this->sent_inputs)) {
                $this->related_chargily_payment->amount = $this->sent_inputs['amount'];
            }

            if (array_key_exists('payment_status', $this->sent_inputs)) {
                $this->related_chargily_payment->status = $this->sent_inputs['payment_status'];
            }

            $this->related_chargily_payment->updated_at = now();

            try {
                $is_updated = $this->related_chargily_payment->save();

                if (!$is_updated) {
                    throw new Exception(
                        "- .",
                        500
                    );
                }

            } catch (Throwable $th) {

                Log::channel('update_order_base_data_errors')->error(
                    "\n\n" .
                    "Description: Failed to update order's related payment.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
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


    }

    private function updateBasicData()
    {
        $this->sent_inputs["updated_at"] = now();

        try {
            $is_updated = $this->requested_order
                ->update($this->sent_inputs);

            if (!$is_updated) {
                throw new Exception(
                    "- .",
                    500
                );
            }

        } catch (Throwable $th) {
            try {
                Log::channel('update_order_base_data_errors')->error(
                    "\n\n" .
                    "Description: Failed to update order.\n\n" .
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
    }

    private function checkModificationsAreMadeAndFilteringSentInputs()
    {
        $this->sent_inputs = $this->global_request_object->validated();

        $original_data = $this->requested_order->getOriginal();

        foreach ($this->sent_inputs as $key => $value) {
            if ($key === "payment_status") {
                if ($value == $this->related_chargily_payment->status) {
                    unset($this->sent_inputs[$key]);
                }
            } else {
                if ($value == $original_data[$key]) {
                    unset($this->sent_inputs[$key]);
                }
            }

        }

        if (count($this->sent_inputs) === 0) {
            try {
                Log::channel('update_order_base_data_errors')->error(
                    "\n\n" .
                    "Description: No updates were made. Please ensure there is at least one modification before submitting.\n\n" .
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
                'No updates were made. Please ensure there is at least one modification before submitting.',
                400
            );
        }
    }


    private function loadRelatedChargilyPayment()
    {
        try {

            $this->related_chargily_payment = ChargilyPayment::where(
                "order_id",
                $this->requested_order->id
            )
                ->lockForUpdate()
                ->first();
        } catch (Throwable $th) {
            try {
                Log::channel('update_order_base_data_errors')->error(
                    "\n\n" .
                    "Description: Failed to get order's related payment from database.\n\n" .
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

        if (!$this->related_chargily_payment) {
            try {
                Log::channel('update_order_base_data_errors')->error(
                    "\n\n" .
                    "Description: Related payment not found.\n\n" .
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

            throw new Exception('Related payment not found.', 404);
        }

        $this->old_related_chargily_payment = clone $this->related_chargily_payment;
    }


    private function loadRequestedOrder()
    {
        try {

            $this->requested_order = Order::where(
                "id",
                $this->global_request_object->order_id
            )
                ->lockForUpdate()
                ->first();
        } catch (Throwable $th) {
            try {
                Log::channel('update_order_base_data_errors')->error(
                    "\n\n" .
                    "Description: Failed to get requested order from database.\n\n" .
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

        if (!$this->requested_order) {
            try {
                Log::channel('update_order_base_data_errors')->error(
                    "\n\n" .
                    "Description: Requested order not found.\n\n" .
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
                'Requested order not found.',
                404
            );
        }

        $this->old_requested_order = clone $this->requested_order;
    }


    private function logRequest()
    {
        try {
            Log::channel('update_order_base_data_requests')->info(
                "\n\n" .
                "Description: Order's base data updated successfully.\n\n" .
                "Old Order Data: \n" .
                json_encode($this->old_requested_order->toArray(), JSON_PRETTY_PRINT) . "\n\n" .
                "Changes: \n" .
                json_encode($this->requested_order->getChanges(), JSON_PRETTY_PRINT) . "\n\n" .
                "Old Payment Data: \n" .
                json_encode($this->old_related_chargily_payment->toArray(), JSON_PRETTY_PRINT) . "\n\n" .
                "Changes: \n" .
                json_encode($this->related_chargily_payment->getChanges(), JSON_PRETTY_PRINT) . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );
        } catch (Throwable $th) {
            //throw $th;
        }

    }


    public function __invoke(UpdateOrderBaseDataRequest $request)
    {
        $this->global_request_object = $request;

        DB::transaction(function () {
            $this->loadRequestedOrder();

            $this->loadRelatedChargilyPayment();

            $this->checkModificationsAreMadeAndFilteringSentInputs();

            $this->updateBasicData();

            $this->updateChargilyPayment();
        });

        $this->logRequest();

        return response()->json([
            'message' => "Order's base data updated successfully.",
        ], 200);
    }
}
