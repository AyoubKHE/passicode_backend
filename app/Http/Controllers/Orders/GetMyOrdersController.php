<?php

namespace App\Http\Controllers\Orders;

use Exception;
use Throwable;
use App\Models\Orders\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

            Log::channel('get_my_orders_errors')->error(
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

        try {

            $this->client_paginated_orders = Order::where(
                "user_id",
                $this->global_request_object->get('logged_in_user')->id
            )
                ->where(function ($query) {
                    $query->where("status", "completed")
                        ->orWhere("status", "processing")
                        ->orWhere("status", "under_review")
                        ->orWhere("status", "partially_refunded")
                        ->orWhere("status", "refunded");
                })
                ->with('category')
                ->with('chargilyPayment')
                ->with([
                    'orderItems' => function ($query) {
                        $query->whereHas('product', function ($query) {
                            $query->where('status', 'valid');
                        })->with('product');
                    }
                ])
                ->orderBy('created_at', 'desc')
                ->paginate(perPage: $limit, page: $page);
        } catch (Throwable $th) {

            Log::channel('get_my_orders_errors')->error(
                "\n\n" .
                "Description: Failed to get user's orders from database.\n\n" .
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

    private function logRequest()
    {
        try {
            Log::channel('get_my_orders_requests')->info(
                "\n\n" .
                "Description: User has viewed his orders.\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Page: " . (int) $this->global_request_object->get('page', 1) . "\n\n" .
                "Limit: " . (int) $this->global_request_object->get('limit', 10) . "\n\n" .
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

        $this->prepareClientPaginatedOrders();

        $this->logRequest();

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
