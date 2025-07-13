<?php

namespace App\Http\Controllers\Orders;

use Exception;
use Throwable;
use App\Models\Orders\Order;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\Orders\SimpleOrdersCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Http\Requests\Orders\GetPaginatedOrdersByFilterRequest;

class GetPaginatedOrdersByFilterController extends Controller
{
    private GetPaginatedOrdersByFilterRequest $global_request_object;
    private LengthAwarePaginator $paginated_orders;
    private Builder $order_filter_query;
    private array $sent_filter;

    private function prepareCreatedAt()
    {
        $this->order_filter_query->when(
            array_key_exists('created_at', $this->sent_filter),
            function (Builder $query) {
                if (array_key_exists("from", $this->sent_filter['created_at'])) {
                    $min_created_at = $this->sent_filter['created_at']['from'];
                } else {
                    try {
                        $min_created_at = Order::min("created_at");
                    } catch (Throwable $th) {
                        throw new Exception(
                            'An error occurred while accessing the database. Please try again later.',
                            500
                        );
                    }

                }

                if (array_key_exists("to", $this->sent_filter['created_at'])) {
                    $max_created_at = $this->sent_filter['created_at']['to'];
                } else {
                    try {
                        $max_created_at = Order::max("created_at");
                    } catch (Throwable $th) {
                        throw new Exception(
                            'An error occurred while accessing the database. Please try again later.',
                            500
                        );
                    }

                }

                $query->whereBetween('created_at', [$min_created_at, $max_created_at]);
            }
        );
    }

    private function prepareAmount()
    {
        $this->order_filter_query->when(
            array_key_exists('amount', $this->sent_filter),
            function (Builder $query) {
                if (array_key_exists("from", $this->sent_filter['amount'])) {
                    $min_amount = $this->sent_filter['amount']['from'];
                } else {
                    try {
                        $min_amount = Order::min("amount");
                    } catch (Throwable $th) {
                        throw new Exception(
                            'An error occurred while accessing the database. Please try again later.',
                            500
                        );
                    }
                }

                if (array_key_exists("to", $this->sent_filter['amount'])) {
                    $max_amount = $this->sent_filter['amount']['to'];
                } else {
                    try {
                        $max_amount = Order::max("amount");
                    } catch (Throwable $th) {
                        throw new Exception(
                            'An error occurred while accessing the database. Please try again later.',
                            500
                        );
                    }
                }

                $query->whereBetween('amount', [$min_amount, $max_amount]);
            }
        );
    }

    private function prepareType()
    {
        $this->order_filter_query->when(
            array_key_exists('type', $this->sent_filter),
            function (Builder $query): void {
                $query->where('type', $this->sent_filter['type']);
            }
        );
    }

    private function prepareStatus()
    {
        $this->order_filter_query->when(
            array_key_exists('status', $this->sent_filter),
            function (Builder $query): void {
                $query->where('status', $this->sent_filter['status']);
            }
        );
    }

    private function prepareProduct()
    {
        $this->order_filter_query->when(
            array_key_exists('product_id', $this->sent_filter),
            function (Builder $query) {
                $query->whereHas(
                    'orderItems',
                    function (Builder $query) {
                        $query->where('product_id', $this->sent_filter['product_id']);
                    }
                );
            }
        );
    }

    private function prepareCategory()
    {
        $this->order_filter_query->when(
            array_key_exists('category_id', $this->sent_filter),
            function (Builder $query): void {
                $query->where('category_id', $this->sent_filter['category_id']);
            }
        );


        // $this->order_filter_query->when(
        //     array_key_exists('category_id', $this->sent_filter),
        //     function (Builder $query) {
        //         $query->whereHas(
        //             'orderItems',
        //             function (Builder $query) {
        //                 $query->whereHas(
        //                     'product',
        //                     function (Builder $query) {
        //                         $query->where('category_id', $this->sent_filter['category_id']);
        //                     }
        //                 );
        //             }
        //         );
        //     }
        // );
    }

    private function prepareUser()
    {
        $this->order_filter_query->when(
            array_key_exists('user_id', $this->sent_filter),
            function (Builder $query) {
                $query->where(
                    'user_id',
                    $this->sent_filter['user_id']
                );
            }
        );
    }

    private function preparePublicId()
    {
        $this->order_filter_query->when(
            array_key_exists('public_id', $this->sent_filter),
            function (Builder $query) {
                $query->where(
                    'public_id',
                    'like',
                    $this->sent_filter['public_id'] . "%"
                );
            }
        );
    }

    private function prepareId()
    {
        $this->order_filter_query->when(
            array_key_exists('id', $this->sent_filter),
            function (Builder $query) {
                $query->where(
                    'id',
                    'like',
                    "%" . $this->sent_filter['id'] . "%"
                );
            }
        );
    }

    private function preparePaginatedOrders()
    {
        $page = (int) $this->global_request_object->get('page', 1);
        $limit = (int) $this->global_request_object->get('limit', 10);

        if ($limit > 100) {
            throw new Exception(
                'Limit must not exceed 100 to ensure optimal performance.',
                400
            );
        }

        $this->sent_filter = $this->global_request_object->validated();

        $this->order_filter_query = Order::query();

        $this->prepareId();

        $this->preparePublicId();

        $this->prepareUser();

        $this->prepareCategory();

        $this->prepareProduct();

        $this->prepareStatus();

        $this->prepareType();

        $this->prepareAmount();

        $this->prepareCreatedAt();

        // $sql = $this->order_filter_query->toRawSql();

        $order_by = "desc";

        if (array_key_exists('status', $this->sent_filter)) {
            if (
                $this->sent_filter['status'] === "processing" ||
                $this->sent_filter['status'] === "under_review" ||
                $this->sent_filter['status'] === "pending"
            ) {
                $order_by = "asc";
            }
        }

        try {
            $this->paginated_orders = $this->order_filter_query
                ->with('user')
                ->with('category')
                ->with('orderItems', function ($query) {
                    $query->with('product');
                })
                ->orderBy('created_at', $order_by)
                ->paginate(perPage: $limit, page: $page);
        } catch (Throwable $th) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

    }

    public function __invoke(GetPaginatedOrdersByFilterRequest $request)
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
