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
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Chargily\ChargilyPay\ChargilyPay;
use App\Models\Orders\ChargilyPayment;
use Chargily\ChargilyPay\Auth\Credentials;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Products\FailedQuantityRequest;
use App\Http\Requests\Orders\OrderCreationRequest;
use Chargily\ChargilyPay\Elements\CheckoutElement;
use Illuminate\Support\Facades\Http;


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
                "success_url" => config('app.url') . "/payment/success",
                "failure_url" => config('app.url') . "/payment/failure",
                "webhook_endpoint" => config('app.url') . "/api/chargilypay/webhook",
            ]);

            if (!$this->checkout) {
                throw new Exception(
                    "- .",
                    500
                );
            }

        } catch (Throwable $th) {

            Log::channel('order_creation_errors')->error(
                "\n\n" .
                "Description: Failed to create chargily checkout.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Order ID: " . $this->order->id . "\n\n" .
                "Payment ID: " . $this->chargily_payment->id . "\n\n" .
                "Category ID: " . $this->received_data['category_id'] . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

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

            if (!$is_created) {
                throw new Exception(
                    "- .",
                    500
                );
            }

        } catch (Throwable $th) {

            Log::channel('order_creation_errors')->error(
                "\n\n" .
                "Description: Failed to create payment in database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Order ID: " . $this->order->id . "\n\n" .
                "Category ID: " . $this->received_data['category_id'] . "\n\n" .
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

                if (!$is_created) {
                    throw new Exception(
                        "- .",
                        500
                    );
                }

            } catch (Throwable $th) {

                Log::channel('order_creation_errors')->error(
                    "\n\n" .
                    "Description: Failed to create order item in database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Order ID: " . $this->order->id . "\n\n" .
                    "Product ID: " . $product->id . "\n\n" .
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

            if (!$is_created) {
                throw new Exception(
                    "- .",
                    500
                );
            }

        } catch (Throwable $th) {

            Log::channel('order_creation_errors')->error(
                "\n\n" .
                "Description: Failed to create order in database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Category ID: " . $this->received_data['category_id'] . "\n\n" .
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

    private function updateProductsSoldStatus()
    {
        foreach ($this->products as $product) {
            $product->sold = 1;
            $product->updated_at = now();
            try {
                $is_updated = $product->save();

                if (!$is_updated) {
                    throw new Exception(
                        "- .",
                        500
                    );
                }

            } catch (Throwable $th) {

                Log::channel('order_creation_errors')->error(
                    "\n\n" .
                    "Description: Failed to update product sold status in database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Product ID: " . $product->id . "\n\n" .
                    "Category ID: " . $this->received_data['category_id'] . "\n\n" .
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
    }

    private function updateRequestedCategoryQuantity()
    {
        $this->requested_category->quantity -= (int) $this->received_data['quantity'];
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

            Log::channel('order_creation_errors')->error(
                "\n\n" .
                "Description: Failed to update requested category quantity in database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Category ID: " . $this->received_data['category_id'] . "\n\n" .
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

            Log::channel('order_creation_errors')->error(
                "\n\n" .
                "Description: Failed to get products from database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Category ID: " . $this->received_data['category_id'] . "\n\n" .
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
                ->with("parentCategory")
                ->lockForUpdate()
                ->first();
        } catch (Throwable $th) {

            Log::channel('order_creation_errors')->error(
                "\n\n" .
                "Description: Failed to get requested category from database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Category ID: " . $this->received_data['category_id'] . "\n\n" .
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

            Log::channel('order_creation_errors')->error(
                "\n\n" .
                "Description: Requested category not found.\n\n" .
                "Error message: - .\n\n" .
                "Category ID: " . $this->received_data['category_id'] . "\n\n" .
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

        if (!$this->requested_category->is_active) {

            Log::channel('order_creation_errors')->error(
                "\n\n" .
                "Description: Requested category is not active.\n\n" .
                "Error message: - .\n\n" .
                "Category ID: " . $this->received_data['category_id'] . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'Requested category is not available.',
                422
            );
        }

        if (!$this->requested_category->is_leaf_category) {

            Log::channel('order_creation_errors')->error(
                "\n\n" .
                "Description: Requested category is not a leaf category.\n\n" .
                "Error message: - .\n\n" .
                "Category ID: " . $this->received_data['category_id'] . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'Requested category is not a leaf category.',
                422
            );
        }

        if (
            (double) $this->requested_category->price * (int) $this->received_data['quantity'] >
            config("app.MAX_AMOUNT")
        ) {

            Log::channel('order_creation_errors')->error(
                "\n\n" .
                "Description: Order amount exceed allowed max amount.\n\n" .
                "Error message: - .\n\n" .
                "Category ID: " . $this->received_data['category_id'] . "\n\n" .
                "Category Price: " . (double) $this->requested_category->price . "\n\n" .
                "Received quantity: " . (int) $this->received_data['quantity'] . "\n\n" .
                "Order amount: " . $this->requested_category->price * (int) $this->received_data['quantity'] . "\n\n" .
                "Max Amount: " . config("app.MAX_AMOUNT") . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'Requested category is not available.',
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

            Log::channel('order_creation_errors')->error(
                "\n\n" .
                "Description: Failed to create FailedQuantityRequest record in database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
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
    private function isAdminAvailableForBackorder()
    {
        try {

            $is_admin_available_for_backorder = Setting::where(
                "key",
                "is_admin_available_for_backorder"
            )
                ->value('value');
        } catch (Throwable $th) {

            Log::channel('order_creation_errors')->error(
                "\n\n" .
                "Description: Failed to get is_admin_available_for_backorder setting from database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Category ID: " . $this->received_data['category_id'] . "\n\n" .
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

        if ($is_admin_available_for_backorder === "true") {
            return true;
        } else {

            Log::channel('order_creation_errors')->error(
                "\n\n" .
                "Description: Admin is not available for backorder.\n\n" .
                "Error message: - .\n\n" .
                "Category ID: " . $this->received_data['category_id'] . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'Requested category is not available.',
                422
            );
        }
    }


    private function getCategoryFromOneClickDz(string $oneclickdz_category_id, string $oneclickdz_parent_category_id)
    {
        try {
            $response = Http::withHeaders([
                'X-Access-Token' => config('app.ONECLICKDZ_API_TOKEN')
            ])->get("https://api.oneclickdz.com/v3/gift-cards/checkProduct/" . $oneclickdz_parent_category_id);

            if ($response->failed()) {

                throw new Exception(
                    '- .',
                    500
                );
            }
        } catch (Throwable $th) {

            Log::channel('order_creation_errors')->error(
                "\n\n" .
                "Description: Failed to get category from oneclickdz.\n\n" .
                "Error message: - .\n\n" .
                "Category ID: " . $this->received_data['category_id'] . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'Requested category is not available.',
                422
            );
        }

        $child_categories = $response->json()["data"]["types"];

        foreach ($child_categories as $child_category) {

            if ($child_category["id"] === $oneclickdz_category_id) {
                return $child_category;
            }

        }

        try {
            Log::channel('order_creation_errors')->error(
                "\n\n" .
                "Description: Category not found at oneclickdz.\n\n" .
                "Error message: - .\n\n" .
                "Category ID: " . $this->received_data['category_id'] . "\n\n" .
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
            'Requested category is not available.',
            422
        );
    }
    private function getParentCategoryFromOneClickDz(string $oneclickdz_parent_category_id)
    {
        try {
            $response = Http::withHeaders([
                'X-Access-Token' => config('app.ONECLICKDZ_API_TOKEN')
            ])->get("https://api.oneclickdz.com/v3/gift-cards/catalog");

            if ($response->failed()) {

                throw new Exception(
                    '- .',
                    500
                );
            }
        } catch (Throwable $th) {

            Log::channel('order_creation_errors')->error(
                "\n\n" .
                "Description: Failed to get parent category from oneclickdz.\n\n" .
                "Error message: - .\n\n" .
                "Category ID: " . $this->received_data['category_id'] . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'Requested category is not available.',
                422
            );
        }

        $oneclickdz_catalog = $response->json();

        foreach ($oneclickdz_catalog["data"]["categories"] as $oneclickdz_category) {

            if ($oneclickdz_category["title"] === "Mobile & Internet") {
                continue;
            }

            foreach ($oneclickdz_category["products"] as $oneclickdz_product) {

                if ($oneclickdz_product["id"] === $oneclickdz_parent_category_id) {
                    return $oneclickdz_product;
                }

            }
        }

        try {
            Log::channel('order_creation_errors')->error(
                "\n\n" .
                "Description: Parent category not found at oneclickdz.\n\n" .
                "Error message: - .\n\n" .
                "Category ID: " . $this->received_data['category_id'] . "\n\n" .
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
            'Requested category is not available.',
            422
        );
    }
    private function isCategoryAvailableAtOneClickDz()
    {
        $oneclickdz_parent_category = $this->getParentCategoryFromOneClickDz(
            $this->requested_category->parentCategory->oneclickdz_id
        );

        if ($oneclickdz_parent_category["enabled"] === false) {
            Log::channel('order_creation_errors')->error(
                "\n\n" .
                "Description: Parent category is not enabled at oneclickdz.\n\n" .
                "Error message: - .\n\n" .
                "Category ID: " . $this->received_data['category_id'] . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'Requested category is not available.',
                422
            );
        }

        $oneclickdz_category = $this->getCategoryFromOneClickDz(
            $this->requested_category->oneclickdz_id,
            $this->requested_category->parentCategory->oneclickdz_id
        );

        if ($oneclickdz_category["quantity"] < 20) {
            Log::channel('order_creation_errors')->error(
                "\n\n" .
                "Description: Category quantity at oneclickdz is less than 20.\n\n" .
                "Error message: - .\n\n" .
                "Category ID: " . $this->received_data['category_id'] . "\n\n" .
                "Quantity At OneClickDz: " . $oneclickdz_category["quantity"] . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'Requested category is not available.',
                422
            );
        }

        return true;
    }

    private function logRequest()
    {
        try {

            $information = "";
            if ($this->order->type === "instock") {

                $information = "\n\n" .
                    "Description: Order created successfully.\n\n" .
                    "Order Data: \n" .
                    json_encode($this->order->toArray(), JSON_PRETTY_PRINT) . "\n\n" .
                    "Products Ids: \n{ \n\t" .
                    $this->products->map(function ($product) {
                        return $product->id;
                    })->implode(', ') .
                    " \n}\n\n" .
                    "Payment Data: \n" .
                    json_encode($this->chargily_payment->toArray(), JSON_PRETTY_PRINT) . "\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n";

            } else {
                $information = "\n\n" .
                    "Description: Order created successfully.\n\n" .
                    "Order Data: \n" .
                    json_encode($this->order->toArray(), JSON_PRETTY_PRINT) . "\n\n" .
                    "Payment Data: \n" .
                    json_encode($this->chargily_payment->toArray(), JSON_PRETTY_PRINT) . "\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n";
            }

            Log::channel('order_creation_requests')->info($information);
        } catch (Throwable $th) {
            //throw $th;
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
                    if (
                        $this->isAdminAvailableForBackorder() &&
                        $this->isCategoryAvailableAtOneClickDz()
                    ) {
                        $this->createOrder("backorder");
                        $this->createChargilyPayment();
                        $this->createCheckout();
                    }
                }
            });

            $this->logRequest();

            return response()->json([
                'message' => 'Order created successfully.',
                'payment_redirection_url' => (string) $this->checkout->getUrl(),
            ], 201);

        } catch (Throwable $th) {
            if ($th->getMessage() === "Requested category is not available.") {
                $this->logFailedQuantityRequest();
            }

            throw $th;
        }
    }
}
