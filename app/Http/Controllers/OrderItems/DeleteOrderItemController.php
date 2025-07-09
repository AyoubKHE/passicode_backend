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

    private function updateProductSoldStatus()
    {
        $this->requested_product->sold = 0;
        $this->requested_product->updated_at = now();
        try {
            $is_updated = $this->requested_product->save();
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

    private function deleteRequestedOrderItem()
    {
        try {
            $is_deleted = $this->requested_order_item->delete();
        } catch (Throwable $throwable) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$is_deleted) {
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
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$this->requested_category) {
            throw new Exception('Requested category not found.', 404);
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
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$this->requested_product) {
            throw new Exception('Requested product not found.', 404);
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
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$this->requested_order_item) {
            throw new Exception('Requested order item not found.', 404);
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
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$this->requested_order) {
            throw new Exception('Requested order not found.', 404);
        }

        if ($this->requested_order->status === "completed") {
            throw new Exception('You cannot remove items from an order that is marked as completed.', 422);
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

        return response()->json([
            'message' => 'Order item deleted successfully.',
        ], 200);

    }
}
