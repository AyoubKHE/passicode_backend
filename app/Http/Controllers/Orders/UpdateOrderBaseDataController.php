<?php

namespace App\Http\Controllers\Orders;

use Exception;
use Throwable;
use App\Models\Orders\Order;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\UpdateOrderBaseDataRequest;


class UpdateOrderBaseDataController extends Controller
{
    private UpdateOrderBaseDataRequest $global_request_object;
    private Order|null $requested_order;
    private array $sent_inputs;


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
            if ($value == $original_data[$key]) {
                unset($this->sent_inputs[$key]);
            }
        }

        if (count($this->sent_inputs) === 0) {
            throw new Exception(
                'No updates were made. Please ensure there is at least one modification before submitting.',
                400
            );
        }
    }


    private function loadRequestedOrder()
    {
        try {

            $this->requested_order = Order::where(
                "id",
                $this->global_request_object->order_id
            )->first();
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

        $this->loadRequestedOrder();

        $this->checkModificationsAreMadeAndFilteringSentInputs();

        $this->updateBasicData();

        return response()->json([
            'message' => "Order's base data updated successfully.",
        ], 200);
    }
}
