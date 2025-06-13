<?php

namespace App\Http\Controllers\Orders;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Models\Orders\Order;
use App\Http\Controllers\Controller;
use App\Http\Resources\Orders\OrderResource;

class GetOrderByIdController extends Controller
{
    private Request $global_request_object;
    private Order|null $requested_order;

    private function loadRequestedOrder()
    {
        try {
            $this->requested_order = Order::where(
                "id",
                $this->global_request_object->order_id
            )
                ->with('user')
                ->with('chargilyPayment')
                ->with('orderItems', function ($query) {
                    $query->with('product', function ($query) {
                        $query->with('category');
                    });
                })
                ->first();
        } catch (Throwable $th) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }
    }

    public function __invoke(Request $request)
    {
        $this->global_request_object = $request;

        $this->loadRequestedOrder();

        if ($this->requested_order) {
            return response()->json([
                'order' => new OrderResource($this->requested_order),
            ], 200);
        } else {
            return response()->json([
                'order' => null,
            ], 200);
        }
    }
}
