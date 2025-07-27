<?php

namespace App\Http\Controllers\Orders;

use Exception;
use Throwable;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Products\Product;
use App\Models\Settings\Setting;
use App\Models\Products\Category;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Chargily\ChargilyPay\ChargilyPay;
use App\Models\Orders\ChargilyPayment;
use Chargily\ChargilyPay\Auth\Credentials;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Products\FailedQuantityRequest;
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

    protected function chargilyPayInstance()
    {
        return new ChargilyPay(new Credentials([
            "mode" => "test",
            "public" => config('app.CHARGILY_PUBLIC_KEY'),
            "secret" => config('app.CHARGILY_SECRET_KEY'),
        ]));
    }
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
                "success_url" => env('APP_URL') . "/payment/success",
                "failure_url" => env('APP_URL') . "/payment/failure",
                "webhook_endpoint" => env('APP_URL') . "/api/chargilypay/webhook",
            ]);
        } catch (Throwable $th) {
            throw new Exception(
                'An error occurred while creating chargily checkout. Please try again later.',
                500
            );
        }

        if (!$this->checkout) {
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

    private function createOrder(string $type)
    {
        $this->order = new Order();
        $this->order->public_id = (string) Str::ulid();
        $this->order->user_id = $this->global_request_object->get('logged_in_user')->id;
        $this->order->category_id = $this->requested_category->id;
        $this->order->quantity = (int) $this->received_data['quantity'];
        $this->order->status = 'pending';
        $this->order->amount = (int) $this->requested_category->discount ?
            ((float) $this->requested_category->price - (float) $this->requested_category->price * (int) $this->requested_category->discount / 100)
            * (int) $this->received_data['quantity'] :
            (float) $this->requested_category->price
            * (int) $this->received_data['quantity'];

        $this->order->amount = round((float) $this->order->amount, 2);

        $this->order->type = $type;

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
                ->where('status', 'valid')
                ->where('expiration_date', '>=', Carbon::now()->toDateString())
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

    private function isQuantityAvailableInStock()
    {
        return $this->requested_category->quantity >= (int) $this->received_data['quantity'];
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

        if (!$this->requested_category->is_active) {
            throw new Exception(
                'Requested category is not available.',
                422
            );
        }

        if (!$this->requested_category->is_leaf_category) {
            throw new Exception(
                'Requested category is not a leaf category.',
                422
            );
        }
    }

    private function logFailedQuantityRequest()
    {
        try {
            FailedQuantityRequest::create([
                'category_id' => $this->requested_category->id,
                'user_id' => $this->global_request_object->get('logged_in_user')->id,
                'available_quantity' => $this->requested_category->quantity,
                'requested_quantity' => (int) $this->received_data['quantity'],
                'is_category_active' => $this->requested_category->is_active,
                'status' => 'not_settled',
                'created_at' => now(),
                'settled_at' => null,
            ]);
        } catch (Throwable $th) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }
    }
    private function isAdminAvailableForBackorder()
    {
        try {

            $is_admin_available_for_backorder = Setting::where(
                "key",
                "is_admin_available_for_backorder"
            )
                ->value('value');
        } catch (Throwable $th) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if ($is_admin_available_for_backorder === "true") {
            return true;
        } else {
            throw new Exception(
                'Requested category is not available.',
                422
            );
        }
    }

    public function __invoke(OrderCreationRequest $global_request_object)
    {
        $this->global_request_object = $global_request_object;

        $this->received_data = $this->global_request_object->validated();

        try {
            DB::transaction(function () {
                $this->loadRequestedCategory();
                if ($this->isQuantityAvailableInStock()) {
                    $this->loadProducts();
                    $this->updateRequestedCategoryQuantity();
                    $this->updateProductsSoldStatus();
                    $this->createOrder("instock");
                    $this->createOrderItems();
                    $this->createChargilyPayment();
                    $this->createCheckout();
                } else {
                    if ($this->isAdminAvailableForBackorder()) {
                        $this->createOrder("backorder");
                        $this->createChargilyPayment();
                        $this->createCheckout();
                    }
                }
            });
        } catch (Throwable $th) {
            if ($th->getMessage() === "Requested category is not available.") {
                $this->logFailedQuantityRequest();
            }

            throw $th;
        }


        return response()->json([
            'message' => 'Order created successfully.',
            'payment_redirection_url' => (string) $this->checkout->getUrl(),
        ], 201);

    }
}
