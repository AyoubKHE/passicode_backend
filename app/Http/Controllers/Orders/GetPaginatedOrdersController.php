<?php

namespace App\Http\Controllers\Orders;

use Exception;
use Throwable;
use App\Models\Orders\Order;
use Illuminate\Http\Request;
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
                    ->with('orderItems', function ($query) {
                        $query->with('product');
                    })
                    ->orderBy('created_at', 'desc')
                    ->paginate(perPage: $limit, page: $page);
            } catch (Throwable $th) {
                throw new Exception(
                    'An error occurred while accessing the database. Please try again later.',
                    500
                );
            }
        } else {
            try {
                $this->paginated_orders = Order::with('user')
                    ->with('orderItems', function ($query) {
                        $query->with('product');
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
