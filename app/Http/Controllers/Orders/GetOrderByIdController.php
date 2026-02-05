<?php

namespace App\Http\Controllers\Orders;

use Exception;
use Throwable;
use App\Models\Orders\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\Orders\DashboardOrderResource;

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
                ->with('category')
                ->with('chargilyPayment')
                ->with('orderItems', function ($query) {
                    $query->with('product');
                })
                ->first();
        } catch (Throwable $th) {
            try {
                Log::channel('get_order_by_id_errors')->error(
                    "\n\n" .
                    "Description: Failed to get order by id from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Order ID: " . $this->global_request_object->order_id . "\n\n" .
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

    public function __invoke(Request $request)
    {
        $this->global_request_object = $request;

        $this->loadRequestedOrder();

        if ($this->requested_order) {
            return response()->json([
                'order' => new DashboardOrderResource($this->requested_order),
            ], 200);
        } else {
            return response()->json([
                'order' => null,
            ], 200);
        }
    }
}
