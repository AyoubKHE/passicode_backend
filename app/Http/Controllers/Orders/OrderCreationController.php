<?php

namespace App\Http\Controllers\Orders;

use Exception;
use Throwable;
use Illuminate\Support\Str;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Products\Product;
use App\Models\Products\Category;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Chargily\ChargilyPay\ChargilyPay;
use App\Models\Orders\ChargilyPayment;
use Chargily\ChargilyPay\Auth\Credentials;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Requests\Orders\OrderCreationRequest;
use Chargily\ChargilyPay\Elements\CheckoutElement;


class OrderCreationController extends Controller
{

    private OrderCreationRequest $global_request_object;
    private Category|null $requested_category;
    private Order|null $order;
    private ChargilyPayment|null $chargily_payment;
    private Collection $products;
    private CheckoutElement|null $checkout;
    private array $received_data;

    private function createCheckout()
    {
        try {
            $this->checkout = $this->chargilyPayInstance()->checkouts()->create([
                "metadata" => [
                    "payment_id" => $this->chargily_payment->id,
                ],
                "locale" => "fr",
                "amount" => $this->chargily_payment->amount,
                "currency" => $this->chargily_payment->currency,
                "description" => "Payment ID={$this->chargily_payment->id}",
                "success_url" => "https://9924-154-247-182-75.ngrok-free.app/payment/success",
                "failure_url" => "https://9924-154-247-182-75.ngrok-free.app/payment/failure",
                // "webhook_endpoint" => route("chargilypay.webhook_endpoint"),
                "webhook_endpoint" => "https://9924-154-247-182-75.ngrok-free.app/api/chargilypay/webhook",
            ]);
        } catch (Throwable $th) {
            throw new Exception(
                'An error occurred while creating chargily checkout. Please try again later.',
                500
            );
        }

    }

    private function createChargilyPayment()
    {
        $this->chargily_payment = new ChargilyPayment();

        $this->chargily_payment->chargily_payment_id = null;
        $this->chargily_payment->user_id = $this->global_request_object->get('logged_in_user')->id;
        $this->chargily_payment->order_id = $this->order->id;
        $this->chargily_payment->status = 'pending';
        $this->chargily_payment->currency = 'dzd';
        $this->chargily_payment->amount = $this->order->amount;
        $this->chargily_payment->created_at = now();
        $this->chargily_payment->updated_at = null;

        try {
            $is_created = $this->chargily_payment->save();
        } catch (Throwable $throwable) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$is_created) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }
    }

    private function createOrderItems()
    {
        foreach ($this->products as $product) {

            $order_item = new OrderItem();

            $order_item->order_id = $this->order->id;
            $order_item->product_id = $product->id;
            $order_item->price = $this->requested_category->price;
            $order_item->discount = $this->requested_category->discount;

            try {
                $is_created = $order_item->save();
            } catch (Throwable $throwable) {
                throw new Exception(
                    'An error occurred while accessing the database. Please try again later.',
                    500
                );
            }

            if (!$is_created) {
                throw new Exception(
                    'An error occurred while accessing the database. Please try again later.',
                    500
                );
            }
        }
    }

    private function createOrder()
    {
        $this->order = new Order();
        $this->order->public_id = (string) Str::ulid();
        $this->order->user_id = $this->global_request_object->get('logged_in_user')->id;
        $this->order->status = 'pending';
        $this->order->amount = (int) $this->requested_category->discount ?
            ((float) $this->requested_category->price - (float) $this->requested_category->price * (int) $this->requested_category->discount / 100)
            * (int) $this->received_data['quantity'] :
            (float) $this->requested_category->price
            * (int) $this->received_data['quantity'];

        $this->order->amount = round((float) $this->order->amount, 2);

        $this->order->created_at = now();
        $this->order->updated_at = null;

        try {
            $is_created = $this->order->save();
        } catch (Throwable $throwable) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$is_created) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }
    }

    private function updateProductsSoldStatus()
    {
        foreach ($this->products as $product) {
            $product->sold = 1;
            $product->updated_at = now();
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

    private function updateRequestedCategoryQuantity()
    {
        $this->requested_category->quantity -= (int) $this->received_data['quantity'];
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

    private function loadProducts()
    {
        try {

            $this->products = Product::where('category_id', $this->received_data['category_id'])
                ->where('sold', false)
                ->orderBy('expiration_date', 'asc')
                ->orderBy('purchase_price', 'asc')
                ->limit($this->received_data['quantity'])
                ->lockForUpdate()
                ->get();
        } catch (Throwable $th) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }
    }

    private function moreValidations()
    {
        if (!$this->requested_category->is_active) {
            throw new Exception(
                'Requested category is not active.',
                422
            );
        }

        if (!$this->requested_category->is_leaf_category) {
            throw new Exception(
                'Requested category is not a leaf category.',
                422
            );
        }

        if ($this->requested_category->quantity < (int) $this->received_data['quantity']) {
            throw new Exception(
                'Requested quantity is not available.',
                422
            );
        }
    }

    private function loadRequestedCategory()
    {
        try {

            $this->requested_category = Category::where(
                "id",
                $this->received_data['category_id']
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
            throw new Exception(
                'Requested category not found.',
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

    public function __invoke(OrderCreationRequest $global_request_object)
    {
        $this->global_request_object = $global_request_object;

        $this->received_data = $this->global_request_object->validated();

        DB::transaction(function () {
            $this->loadRequestedCategory();
            $this->moreValidations();
            $this->loadProducts();
            $this->updateRequestedCategoryQuantity();
            $this->updateProductsSoldStatus();
            $this->createOrder();
            $this->createOrderItems();
            $this->createChargilyPayment();
            $this->createCheckout();
        });

        if ($this->checkout) {
            return response()->json([
                'message' => 'Order created successfully.',
                'payment_redirection_url' => (string) $this->checkout->getUrl(),
            ], 200);
        } else {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }
    }
}
