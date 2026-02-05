<?php

namespace App\Http\Controllers\OrderItems;

use Exception;
use Throwable;
use Carbon\Carbon;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Products\Product;
use App\Models\Products\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderItems\OrderItemCreationRequest;


class OrderItemCreationController extends Controller
{
    private OrderItemCreationRequest $global_request_object;
    private array $prepared_order_item;
    private Order|null $requested_order;
    private Product|null $requested_product;
    private Category|null $requested_category;

    private function updateRelatedCategoryQuantity()
    {
        $this->requested_category->quantity -= 1;
        $this->requested_category->updated_at = now();
        try {
            $is_updated = $this->requested_category->save();

            if (!$is_updated) {
                throw new Exception(
                    "- .",
                    500
                );
            }
        } catch (Throwable $th) {
            try {
                Log::channel('order_item_creation_errors')->error(
                    "\n\n" .
                    "Description: Failed to update category's quantity in database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Category ID: " . $this->requested_category->id . "\n\n" .
                    "Order ID: " . $this->prepared_order_item['order_id'] . "\n\n" .
                    "Product ID: " . $this->prepared_order_item['product_id'] . "\n\n" .
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

    private function updateProductSoldStatus()
    {
        $this->requested_product->sold = 1;
        $this->requested_product->updated_at = now();
        try {
            $is_updated = $this->requested_product->save();

            if (!$is_updated) {
                throw new Exception(
                    "- .",
                    500
                );
            }
        } catch (Throwable $th) {
            try {
                Log::channel('order_item_creation_errors')->error(
                    "\n\n" .
                    "Description: Failed to update product's sold status in database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Order ID: " . $this->prepared_order_item['order_id'] . "\n\n" .
                    "Product ID: " . $this->prepared_order_item['product_id'] . "\n\n" .
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

    private function storeOrderItem(): void
    {
        $this->prepared_order_item['price'] = $this->requested_category->price;
        $this->prepared_order_item['discount'] = $this->requested_category->discount;

        try {
            $order_item = OrderItem::create(
                $this->prepared_order_item
            );

            if (!$order_item) {
                throw new Exception(
                    "- .",
                    500
                );
            }
        } catch (Throwable $th) {
            try {
                Log::channel('order_item_creation_errors')->error(
                    "\n\n" .
                    "Description: Failed to store order item in database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Order ID: " . $this->prepared_order_item['order_id'] . "\n\n" .
                    "Product ID: " . $this->prepared_order_item['product_id'] . "\n\n" .
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

    private function loadRequestedCategory()
    {
        try {

            $this->requested_category = Category::where(
                "id",
                $this->requested_product->category_id
            )
                ->lockForUpdate()
                ->first();
        } catch (Throwable $th) {
            try {
                Log::channel('order_item_creation_errors')->error(
                    "\n\n" .
                    "Description: Failed to get requested category from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Category ID: " . $this->requested_product->category_id . "\n\n" .
                    "Order ID: " . $this->prepared_order_item['order_id'] . "\n\n" .
                    "Product ID: " . $this->prepared_order_item['product_id'] . "\n\n" .
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

        if (!$this->requested_category) {
            try {
                Log::channel('order_item_creation_errors')->error(
                    "\n\n" .
                    "Description: Requested category not found.\n\n" .
                    "Error message: - .\n\n" .
                    "Category ID: " . $this->requested_product->category_id . "\n\n" .
                    "Order ID: " . $this->prepared_order_item['order_id'] . "\n\n" .
                    "Product ID: " . $this->prepared_order_item['product_id'] . "\n\n" .
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
                'Requested category not found.',
                404
            );
        }
    }
    private function loadRequestedProduct()
    {
        try {

            $this->requested_product = Product::where(
                "id",
                $this->prepared_order_item['product_id']
            )
                ->lockForUpdate()
                ->first();
        } catch (Throwable $th) {
            try {
                Log::channel('order_item_creation_errors')->error(
                    "\n\n" .
                    "Description: Failed to get requested product from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Order ID: " . $this->prepared_order_item['order_id'] . "\n\n" .
                    "Product ID: " . $this->prepared_order_item['product_id'] . "\n\n" .
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

        if (!$this->requested_product) {
            try {
                Log::channel('order_item_creation_errors')->error(
                    "\n\n" .
                    "Description: Requested product not found.\n\n" .
                    "Error message: - .\n\n" .
                    "Order ID: " . $this->prepared_order_item['order_id'] . "\n\n" .
                    "Product ID: " . $this->prepared_order_item['product_id'] . "\n\n" .
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
                'Requested product not found.',
                404
            );
        }

        if ($this->requested_product->sold) {
            try {
                Log::channel('order_item_creation_errors')->error(
                    "\n\n" .
                    "Description: The requested product has already been sold.\n\n" .
                    "Error message: - .\n\n" .
                    "Order ID: " . $this->prepared_order_item['order_id'] . "\n\n" .
                    "Product ID: " . $this->prepared_order_item['product_id'] . "\n\n" .
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
                'The requested product has already been sold.',
                422
            );
        }

        if ($this->requested_product->status !== "valid") {
            try {
                Log::channel('order_item_creation_errors')->error(
                    "\n\n" .
                    "Description: Requested product is not valid.\n\n" .
                    "Error message: - .\n\n" .
                    "Order ID: " . $this->prepared_order_item['order_id'] . "\n\n" .
                    "Product ID: " . $this->prepared_order_item['product_id'] . "\n\n" .
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
                'Requested product is not valid.',
                422
            );
        }

        if (
            Carbon::parse($this->requested_product->expiration_date)
                ->lessThan(Carbon::now()->toDateString())
        ) {
            try {
                Log::channel('order_item_creation_errors')->error(
                    "\n\n" .
                    "Description: Requested product is expired.\n\n" .
                    "Error message: - .\n\n" .
                    "Order ID: " . $this->prepared_order_item['order_id'] . "\n\n" .
                    "Product ID: " . $this->prepared_order_item['product_id'] . "\n\n" .
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
                'Requested product is expired.',
                422
            );
        }
    }
    private function loadRequestedOrder()
    {
        try {

            $this->requested_order = Order::where(
                "id",
                $this->prepared_order_item['order_id']
            )
                ->lockForUpdate()
                ->first();
        } catch (Throwable $th) {
            try {
                Log::channel('order_item_creation_errors')->error(
                    "\n\n" .
                    "Description: Failed to get requested order from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Order ID: " . $this->prepared_order_item['order_id'] . "\n\n" .
                    "Product ID: " . $this->prepared_order_item['product_id'] . "\n\n" .
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
                Log::channel('order_item_creation_errors')->error(
                    "\n\n" .
                    "Description: Requested order not found.\n\n" .
                    "Error message: - .\n\n" .
                    "Order ID: " . $this->prepared_order_item['order_id'] . "\n\n" .
                    "Product ID: " . $this->prepared_order_item['product_id'] . "\n\n" .
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

        if ($this->requested_order->status === "completed") {
            try {
                Log::channel('order_item_creation_errors')->error(
                    "\n\n" .
                    "Description: You cannot add items to an order that is marked as completed.\n\n" .
                    "Error message: - .\n\n" .
                    "Order ID: " . $this->prepared_order_item['order_id'] . "\n\n" .
                    "Product ID: " . $this->prepared_order_item['product_id'] . "\n\n" .
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
                'You cannot add items to an order that is marked as completed.',
                422
            );
        }
    }


    private function checkOrderItemExistance()
    {
        try {

            $order_item = OrderItem::where(
                "order_id",
                $this->prepared_order_item['order_id']
            )
                ->where(
                    "product_id",
                    $this->prepared_order_item['product_id']
                )
                ->first();
        } catch (Throwable $th) {
            try {
                Log::channel('order_item_creation_errors')->error(
                    "\n\n" .
                    "Description: Failed to get requested order item from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Order ID: " . $this->prepared_order_item['order_id'] . "\n\n" .
                    "Product ID: " . $this->prepared_order_item['product_id'] . "\n\n" .
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

        if ($order_item) {
            try {
                Log::channel('order_item_creation_errors')->error(
                    "\n\n" .
                    "Description: Order item already exists.\n\n" .
                    "Error message: - .\n\n" .
                    "Order ID: " . $this->prepared_order_item['order_id'] . "\n\n" .
                    "Product ID: " . $this->prepared_order_item['product_id'] . "\n\n" .
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
                'Order item already exists.',
                422
            );
        }
    }

    private function logRequest()
    {
        try {
            Log::channel('order_item_creation_requests')->info(
                "\n\n" .
                "Description: Order item created successfully\n\n" .
                "Order ID: " . $this->prepared_order_item['order_id'] . "\n\n" .
                "Product ID: " . $this->prepared_order_item['product_id'] . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );
        } catch (Throwable $th) {
            //throw $th;
        }

    }

    public function __invoke(OrderItemCreationRequest $request): JsonResponse
    {
        $this->global_request_object = $request;

        $this->prepared_order_item = $this->global_request_object->validated();

        $this->checkOrderItemExistance();

        DB::transaction(function () {

            $this->loadRequestedOrder();

            $this->loadRequestedProduct();

            $this->loadRequestedCategory();

            $this->storeOrderItem();

            $this->updateProductSoldStatus();

            $this->updateRelatedCategoryQuantity();
        });

        $this->logRequest();

        return response()->json([
            'message' => 'Order item created successfully.',
        ], 201);
    }
}
