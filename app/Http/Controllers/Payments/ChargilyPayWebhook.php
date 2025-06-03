<?php

namespace App\Http\Controllers\Payments;

use Exception;
use Throwable;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Products\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Chargily\ChargilyPay\ChargilyPay;
use App\Models\Orders\ChargilyPayment;
use Chargily\ChargilyPay\Auth\Credentials;
use Illuminate\Database\Eloquent\Collection;
use Chargily\ChargilyPay\Elements\WebhookElement;
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
            try {
                $is_updated = $product->save();
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
    }
    private function updateRelatedCategoryQuantity()
    {
        $this->related_category->quantity += count($this->order_products);

        try {
            $is_updated = $this->related_category->save();
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
    private function cancelOrder($status)
    {
        $this->order->status = $status;
        $this->order->updated_at = now();

        try {
            $is_updated = $this->order->save();
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
    private function cancelChargilyPayment($status)
    {
        $this->chargily_payment->chargily_payment_id = $this->checkout->getId();
        $this->chargily_payment->status = $status;
        $this->chargily_payment->updated_at = now();

        try {
            $is_updated = $this->chargily_payment->save();
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
    private function cancel(string $status)
    {
        if ($this->order->status === "pending") {
            try {

                DB::transaction(function () use ($status) {
                    $this->cancelChargilyPayment($status);

                    $this->cancelOrder($status);

                    $this->updateRelatedCategoryQuantity();

                    $this->updateProductsSoldStatus();
                });
            } catch (Throwable $th) {
                // logging order id
                Log::channel('order_confirmation_fails')->error(
                    "Order cancellation failed\nOrder ID : {$this->order->id}.\nError : {$th->getMessage()}.\n----------------------------------------------------------------------------\n"
                );
            }
        }
    }


    private function confirmOrder()
    {
        $this->order->status = "paid";
        $this->order->updated_at = now();

        try {
            $is_updated = $this->order->save();
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
    private function confirmChargilyPayment()
    {
        $this->chargily_payment->chargily_payment_id = $this->checkout->getId();
        $this->chargily_payment->status = "paid";
        $this->chargily_payment->updated_at = now();

        try {
            $is_updated = $this->chargily_payment->save();
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
                    "Order confirmation failed\nOrder ID : {$this->order->id}.\nError : {$th->getMessage()}.\n----------------------------------------------------------------------------\n"
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
                ->with('order', function ($query) {
                    $query->with('orderItems', function ($query) {
                        $query->with('product', function ($query) {
                            $query->with('category');
                        });
                    });
                })
                ->first();
        } catch (Throwable $th) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$this->chargily_payment) {
            throw new Exception(
                'Payment not found.',
                404
            );
        }

        if (!$this->chargily_payment->order) {
            throw new Exception(
                'Order not found.',
                404
            );
        }

        $this->order = $this->chargily_payment->order;

        if (count($this->order->orderItems) === 0) {
            throw new Exception(
                'Order items not found.',
                404
            );
        }

        $this->order_products = $this->order->orderItems->map(function ($item) {
            return $item->product;
        });

        if (count($this->order_products) === 0) {
            throw new Exception(
                'Order products not found.',
                404
            );
        }

        $this->related_category = $this->order_products[0]->category;

        if (!$this->related_category) {
            throw new Exception(
                'Category not found.',
                404
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
                if ($this->checkout) {
                    // $metadata = $this->checkout->getMetadata();
                    // try {
                    //     $this->chargily_payment = ChargilyPayment::where(
                    //         "id",
                    //         $metadata['payment_id']
                    //     )
                    //         ->first();
                    // } catch (Throwable $th) {
                    //     throw new Exception(
                    //         'An error occurred while accessing the database. Please try again later.',
                    //         500
                    //     );
                    // }

                    // if (!$this->chargily_payment) {
                    //     throw new Exception(
                    //         'Payment not found.',
                    //         404
                    //     );
                    // }

                    // try {
                    //     $this->order = Order::where(
                    //         "id",
                    //         $this->chargily_payment->order_id
                    //     )
                    //         ->first();
                    // } catch (Throwable $th) {
                    //     throw new Exception(
                    //         'An error occurred while accessing the database. Please try again later.',
                    //         500
                    //     );
                    // }

                    // if (!$this->order) {
                    //     throw new Exception(
                    //         'Order not found.',
                    //         404
                    //     );
                    // }

                    $this->loadOrderData();

                    if ($this->checkout->getStatus() === "paid") {
                        $this->confirm();

                        return response()->json(["status" => true, "message" => "Payment has been completed"]);
                    } else {
                        if ($this->checkout->getStatus() === "failed") {
                            $this->cancel("failed");
                        } else if ($this->checkout->getStatus() === "canceled") {
                            $this->cancel("canceled");
                        } else if ($this->checkout->getStatus() === "expired") {
                            $this->cancel("expired");
                        }

                        return response()->json(["status" => true, "message" => "Payment has been canceled"]);
                    }
                }
            }
        }

        return response()->json([
            "status" => false,
            "message" => "Invalid Webhook request",
        ], 403);
    }
}
