<?php

namespace App\Http\Controllers\Orders;

use Exception;
use Throwable;
use App\Models\Orders\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\Orders\SimpleOrdersCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;


class GetPaginatedOrdersController extends Controller
{
    private Request $global_request_object;
    private LengthAwarePaginator $paginated_orders;

    private function preparePaginatedOrders()
    {
        $page = (int) $this->global_request_object->get('page', 1);
        $limit = (int) $this->global_request_object->get('limit', 10);
        $order_public_id = $this->global_request_object->get('order_public_id', "");

        if ($limit > 100) {

            Log::channel('get_paginated_orders_errors')->error(
                "\n\n" .
                "Description: Limit must not exceed 100 to ensure optimal performance.\n\n" .
                "Error message: - .\n\n" .
                "Page: " . $page . "\n\n" .
                "Limit: " . $limit . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'Limit must not exceed 100 to ensure optimal performance.',
                400
            );
        }
        
        if ($order_public_id) {
            try {
                $this->paginated_orders = Order::where(
                    'public_id',
                    'like',
                    $order_public_id . "%"
                )
                    ->with('user')
                    ->with('category')
                    ->with('orderItems', function ($query) {
                        $query->with('product');
                    })
                    ->orderBy('created_at', 'desc')
                    ->paginate(perPage: $limit, page: $page);
            } catch (Throwable $th) {

                Log::channel('get_paginated_orders_errors')->error(
                    "\n\n" .
                    "Description: Failed to get paginated orders by public id from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Page: " . $page . "\n\n" .
                    "Limit: " . $limit . "\n\n" .
                    "Order Public ID: " . $order_public_id . "\n\n" .
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
        } else {
            try {
                $this->paginated_orders = Order::with('user')
                    ->with('category')
                    ->with('orderItems', function ($query) {
                        $query->with('product');
                    })
                    ->orderBy('created_at', 'desc')
                    ->paginate(perPage: $limit, page: $page);
            } catch (Throwable $th) {

                Log::channel('get_paginated_orders_errors')->error(
                    "\n\n" .
                    "Description: Failed to get paginated orders from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Page: " . $page . "\n\n" .
                    "Limit: " . $limit . "\n\n" .
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

    public function __invoke(Request $request)
    {
        $this->global_request_object = $request;

        $this->preparePaginatedOrders();

        if (count($this->paginated_orders) > 0) {
            return response()->json([
                'orders_data' => new SimpleOrdersCollection($this->paginated_orders),
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
