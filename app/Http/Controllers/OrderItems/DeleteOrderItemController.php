<?php

namespace App\Http\Controllers\OrderItems;

use Exception;
use Throwable;
use App\Models\Orders\Order;
use Illuminate\Http\Request;
use App\Models\Orders\OrderItem;
use App\Models\Products\Product;
use App\Models\Products\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class DeleteOrderItemController extends Controller
{
    private Request $global_request_object;
    private Order|null $requested_order;
    private OrderItem|null $requested_order_item;
    private Product|null $requested_product;
    private Category|null $requested_category;

    private function updateRelatedCategoryQuantity()
    {
        $this->requested_category->quantity += 1;
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

            Log::channel('delete_order_item_errors')->error(
                "\n\n" .
                "Description: Failed to update requested category's quantity in database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Order ID: " . $this->global_request_object->order_id . "\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
                "Category ID: " . $this->requested_category->id . "\n\n" .
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

    private function updateProductSoldStatus()
    {
        $this->requested_product->sold = 0;
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

            Log::channel('delete_order_item_errors')->error(
                "\n\n" .
                "Description: Failed to update requested product's sold status in database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Order ID: " . $this->global_request_object->order_id . "\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
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

    private function deleteRequestedOrderItem()
    {
        try {
            $is_deleted = $this->requested_order_item->delete();

            if (!$is_deleted) {
                throw new Exception(
                    "- .",
                    500
                );
            }
        } catch (Throwable $th) {

            Log::channel('delete_order_item_errors')->error(
                "\n\n" .
                "Description: Failed to delete requested order item in database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Order ID: " . $this->global_request_object->order_id . "\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
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

            Log::channel('delete_order_item_errors')->error(
                "\n\n" .
                "Description: Failed to get requested category from database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Order ID: " . $this->global_request_object->order_id . "\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
                "Category ID: " . $this->requested_product->category_id . "\n\n" .
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

        if (!$this->requested_category) {

            Log::channel('delete_order_item_errors')->error(
                "\n\n" .
                "Description: Requested category not found.\n\n" .
                "Error message: - .\n\n" .
                "Order ID: " . $this->global_request_object->order_id . "\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
                "Category ID: " . $this->requested_product->category_id . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

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
                $this->global_request_object->product_id
            )
                ->lockForUpdate()
                ->first();
        } catch (Throwable $th) {

            Log::channel('delete_order_item_errors')->error(
                "\n\n" .
                "Description: Failed to get requested product from database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Order ID: " . $this->global_request_object->order_id . "\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
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

        if (!$this->requested_product) {

            Log::channel('delete_order_item_errors')->error(
                "\n\n" .
                "Description: Requested product not found.\n\n" .
                "Error message: - .\n\n" .
                "Order ID: " . $this->global_request_object->order_id . "\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'Requested product not found.',
                404
            );
        }
    }
    private function loadRequestedOrderItem()
    {
        try {

            $this->requested_order_item = OrderItem::where(
                "order_id",
                $this->global_request_object->order_id
            )
                ->where(
                    "product_id",
                    $this->global_request_object->product_id
                )
                ->lockForUpdate()
                ->first();
        } catch (Throwable $th) {

            Log::channel('delete_order_item_errors')->error(
                "\n\n" .
                "Description: Failed to get requested order item from database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Order ID: " . $this->global_request_object->order_id . "\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
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

        if (!$this->requested_order_item) {

            Log::channel('delete_order_item_errors')->error(
                "\n\n" .
                "Description: Requested order item not found.\n\n" .
                "Error message: - .\n\n" .
                "Order ID: " . $this->global_request_object->order_id . "\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'Requested order item not found.',
                404
            );
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

            Log::channel('delete_order_item_errors')->error(
                "\n\n" .
                "Description: Failed to get requested order from database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Order ID: " . $this->global_request_object->order_id . "\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
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

        if (!$this->requested_order) {

            Log::channel('delete_order_item_errors')->error(
                "\n\n" .
                "Description: Requested order not found.\n\n" .
                "Error message: - .\n\n" .
                "Order ID: " . $this->global_request_object->order_id . "\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'Requested order not found.',
                404
            );
        }

        if ($this->requested_order->status === "completed") {

            Log::channel('delete_order_item_errors')->error(
                "\n\n" .
                "Description: You cannot remove items from an order that is marked as completed.\n\n" .
                "Error message: - .\n\n" .
                "Order ID: " . $this->global_request_object->order_id . "\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'You cannot remove items from an order that is marked as completed.',
                422
            );
        }
    }

    private function logRequest()
    {
        try {
            Log::channel('delete_order_item_requests')->info(
                "\n\n" .
                "Description: Order item deleted successfully.\n\n" .
                "Order ID: " . $this->global_request_object->order_id . "\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
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

        DB::transaction(function () {

            $this->loadRequestedOrder();

            $this->loadRequestedOrderItem();

            $this->loadRequestedProduct();

            $this->loadRequestedCategory();

            $this->deleteRequestedOrderItem();

            $this->updateProductSoldStatus();

            $this->updateRelatedCategoryQuantity();
        });

        $this->logRequest();

        return response()->json([
            'message' => 'Order item deleted successfully.',
        ], 200);

    }
}
