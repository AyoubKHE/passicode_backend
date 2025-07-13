<?php

namespace App\Http\Controllers\Orders;

use Exception;
use Throwable;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Orders\ChargilyPayment;
use App\Http\Requests\Orders\UpdateOrderBaseDataRequest;


class UpdateOrderBaseDataController extends Controller
{
    private UpdateOrderBaseDataRequest $global_request_object;
    private Order|null $requested_order;
    private ChargilyPayment|null $related_chargily_payment;
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


    }

    private function updateBasicData()
    {
        $this->sent_inputs["updated_at"] = now();

        try {
            $is_updated = $this->requested_order
                ->update($this->sent_inputs);
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
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$this->related_chargily_payment) {
            throw new Exception('Related payment not found.', 404);
        }
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
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$this->requested_order) {
            throw new Exception('Requested order not found.', 404);
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



        return response()->json([
            'message' => "Order's base data updated successfully.",
        ], 200);
    }
}
