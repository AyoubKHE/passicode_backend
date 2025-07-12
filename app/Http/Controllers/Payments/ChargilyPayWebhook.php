<?php

namespace App\Http\Controllers\Payments;

use Exception;
use Throwable;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Products\Product;
use App\Models\Products\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Chargily\ChargilyPay\ChargilyPay;
use App\Models\Orders\ChargilyPayment;
use Chargily\ChargilyPay\Auth\Credentials;
use Illuminate\Database\Eloquent\Collection;
use Chargily\ChargilyPay\Elements\CheckoutElement;


class ChargilyPayWebhook extends Controller
{
    private CheckoutElement|null $checkout;

    private Order|null $order;

    private Collection $order_products;

    private Category|null $related_category;

    private ChargilyPayment|null $chargily_payment;


    private function updateProductsSoldStatus()
    {
        foreach ($this->order_products as $product) {
            $product->sold = 0;
            $product->updated_at = now();

            $is_updated = $product->save();

            if (!$is_updated) {
                throw new Exception(
                    '$is_updated variable = false in updateProductsSoldStatus method. in product with id = ' . $product->id . '.',
                    500
                );
            }
        }
    }
    private function updateRelatedCategoryQuantity()
    {
        $this->related_category->quantity += count($this->order_products);
        $this->related_category->updated_at = now();

        $is_updated = $this->related_category->save();

        if (!$is_updated) {
            throw new Exception(
                '$is_updated variable = false in updateRelatedCategoryQuantity method.',
                500
            );
        }
    }
    private function loadOrderRestData()
    {
        $orderItems = OrderItem::where(
            "order_id",
            $this->order->id
        )
            ->lockForUpdate()
            ->get();

        if (count($orderItems) === 0) {
            throw new Exception(
                'Order items not found.',
                404
            );
        }


        $this->order_products = $orderItems->map(function ($item) {
            $product = Product::where(
                "id",
                $item->product_id
            )
                ->lockForUpdate()
                ->first();

            if (!$product) {
                throw new Exception(
                    'Product not found.',
                    404
                );
            }

            return $product;
        });


        if (count($this->order_products) === 0) {
            throw new Exception(
                'Order products not found.',
                404
            );
        }


        $this->related_category = Category::where(
            "id",
            $this->order_products[0]->category_id
        )
            ->lockForUpdate()
            ->first();

        if (!$this->related_category) {
            throw new Exception(
                'Category not found.',
                404
            );
        }
    }
    private function cancelOrder()
    {
        $this->order->status = "failed";
        $this->order->updated_at = now();

        $is_updated = $this->order->save();

        if (!$is_updated) {
            throw new Exception(
                '$is_updated variable = false in cancelOrder method.',
                500
            );
        }
    }
    private function cancelChargilyPayment($status)
    {
        $this->chargily_payment->chargily_payment_id = $this->checkout->getId();
        $this->chargily_payment->status = $status;
        $this->chargily_payment->updated_at = now();

        $is_updated = $this->chargily_payment->save();

        if (!$is_updated) {
            throw new Exception(
                '$is_updated variable = false in cancelChargilyPayment method.',
                500
            );
        }
    }
    private function cancel(string $status)
    {
        if ($this->order->status === "pending") {
            try {

                DB::transaction(function () use ($status) {

                    $this->cancelChargilyPayment($status);

                    $this->cancelOrder();

                    if ($this->order->type === "instock") {
                        $this->loadOrderRestData();

                        $this->updateRelatedCategoryQuantity();

                        $this->updateProductsSoldStatus();
                    }
                });
            } catch (Throwable $th) {
                // logging order id
                Log::channel('order_cancellation_fails')->error(
                    "Order cancellation failed with payment status: {$status}.\nOrder ID : {$this->order->id}.\nError : {$th->getMessage()}\n----------------------------------------------------------------------------\n"
                );

                throw new Exception(
                    'An error occurred while accessing the database.',
                    500
                );
            }
        }
    }


    private function confirmOrder()
    {
        if ($this->order->type === "instock") {
            $this->order->status = "completed";
        } else if ($this->order->type === "backorder") {
            $this->order->status = "processing";
        }

        $this->order->updated_at = now();

        $is_updated = $this->order->save();

        if (!$is_updated) {
            throw new Exception(
                '$is_updated variable = false in confirmOrder method.',
                500
            );
        }
    }
    private function confirmChargilyPayment()
    {
        $this->chargily_payment->chargily_payment_id = $this->checkout->getId();
        $this->chargily_payment->status = "paid";
        $this->chargily_payment->updated_at = now();

        $is_updated = $this->chargily_payment->save();

        if (!$is_updated) {
            throw new Exception(
                '$is_updated variable = false in confirmChargilyPayment method.',
                500
            );
        }
    }
    private function confirm()
    {
        if ($this->order->status === "pending") {
            try {
                DB::transaction(function () {
                    $this->confirmChargilyPayment();

                    $this->confirmOrder();
                });
            } catch (Throwable $th) {
                // logging order id
                Log::channel('order_confirmation_fails')->error(
                    "Order confirmation failed.\nOrder ID : {$this->order->id}.\nError : {$th->getMessage()}\n----------------------------------------------------------------------------\n"
                );

                throw new Exception(
                    'An error occurred while accessing the database.',
                    500
                );
            }
        }
    }


    private function loadOrderData()
    {
        $metadata = $this->checkout->getMetadata();

        try {

            $this->chargily_payment = ChargilyPayment::where(
                "id",
                $metadata['payment_id']
            )
                ->lockForUpdate()
                ->first();

            if (!$this->chargily_payment) {
                throw new Exception(
                    'Payment not found.',
                    404
                );
            }

            $this->order = Order::where(
                "id",
                $this->chargily_payment->order_id
            )
                ->lockForUpdate()
                ->first();

            if (!$this->order) {
                throw new Exception(
                    'Order not found.',
                    404
                );
            }
        } catch (Throwable $th) {
            $checkout_status = $this->checkout->getStatus();

            $log_channel_name = $checkout_status === "paid" ?
                "order_confirmation_fails" : "order_cancellation_fails";

            $error_type = $checkout_status === "paid" ? "confirmation" : "cancellation";

            $error_message = "Order $error_type failed with payment status: {$checkout_status}.\nPayment ID : {$metadata['payment_id']}.\nError : {$th->getMessage()}\n----------------------------------------------------------------------------\n";

            Log::channel($log_channel_name)->error($error_message);

            throw new Exception(
                'An error occurred while accessing the database.',
                500
            );
        }

    }
    protected function chargilyPayInstance()
    {
        return new ChargilyPay(new Credentials([
            "mode" => "test",
            "public" => config('app.CHARGILY_PUBLIC_KEY'),
            "secret" => config('app.CHARGILY_SECRET_KEY'),
        ]));
    }
    public function __invoke()
    {
        $webhook = $this->chargilyPayInstance()->webhook()->get();

        if ($webhook) {
            $this->checkout = $webhook->getData();
            if ($this->checkout && $this->checkout instanceof CheckoutElement) {
                return DB::transaction(function () {

                    $this->loadOrderData();

                    if ($this->checkout->getStatus() === "paid") {
                        $this->confirm();

                        return response()->json(["status" => true, "message" => "Payment has been completed"]);
                    } else {
                        $this->cancel($this->checkout->getStatus());

                        return response()->json(["status" => true, "message" => "Payment has been canceled"]);
                    }
                });
            }
        }

        return response()->json([
            "status" => false,
            "message" => "Invalid Webhook request",
        ], 403);
    }
}
