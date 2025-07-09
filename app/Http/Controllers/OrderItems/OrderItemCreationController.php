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
        $this->requested_product->sold = 1;
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

    private function storeOrderItem(): void
    {
        $this->prepared_order_item['price'] = $this->requested_category->price;
        $this->prepared_order_item['discount'] = $this->requested_category->discount;

        try {
            $order_item = OrderItem::create(
                $this->prepared_order_item
            );
        } catch (Throwable $throwable) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$order_item) {
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
                $this->prepared_order_item['product_id']
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

        if ($this->requested_product->sold) {
            throw new Exception('The requested product has already been sold.', 422);
        }

        if ($this->requested_product->status !== "valid") {
            throw new Exception('Requested product is not valid.', 422);
        }

        if (
            Carbon::parse($this->requested_product->expiration_date)
                ->lessThan(Carbon::now()->toDateString())
        ) {
            throw new Exception('Requested product is expired.', 422);
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
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$this->requested_order) {
            throw new Exception('Requested order not found.', 404);
        }

        if ($this->requested_order->status === "completed") {
            throw new Exception('You cannot add items to an order that is marked as completed.', 422);
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
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if ($order_item) {
            throw new Exception('Order item already exists.', 422);
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

        return response()->json([
            'message' => 'Order item created successfully.',
        ], 201);
    }
}
