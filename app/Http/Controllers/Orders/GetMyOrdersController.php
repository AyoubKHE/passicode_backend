<?php

namespace App\Http\Controllers\Orders;

use Exception;
use Throwable;
use App\Models\Orders\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Orders\OrdersCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetMyOrdersController extends Controller
{
    private Request $global_request_object;
    private LengthAwarePaginator $client_paginated_orders;

    private function prepareClientPaginatedOrders()
    {
        $page = (int) $this->global_request_object->get('page', 1);
        $limit = (int) $this->global_request_object->get('limit', 10);

        if ($limit > 100) {
            throw new Exception(
                'Limit must not exceed 100 to ensure optimal performance.',
                400
            );
        }

        try {

            $this->client_paginated_orders = Order::where(
                "user_id",
                $this->global_request_object->get('logged_in_user')->id
            )
                ->where(
                    "status",
                    "paid"
                )
                ->with('chargilyPayment')
                ->with('orderItems', function ($query) {
                    $query->with('product', function ($query) {
                        $query->with('category');
                    });
                })
                ->orderBy('created_at', 'desc')
                ->paginate(perPage: $limit, page: $page);
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

        $this->prepareClientPaginatedOrders();

        if (count($this->client_paginated_orders) > 0) {
            return response()->json([
                'orders_data' => new OrdersCollection($this->client_paginated_orders),
            ], 200);
        } else {
            return response()->json([
                'orders_data' => [
                    'orders' => [],
                    'meta' => null
                ],
            ], 200);
        }
    }
}
