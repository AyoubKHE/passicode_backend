<?php

namespace App\Http\Controllers\Categories;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Models\Products\Category;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class getCategoryPriceController extends Controller
{
    private Request $global_request_object;
    private Category|null $requested_category;

    private function calculateCustomerPriceTTC(float $supplier_price): float
    {
        $margin = config('app.MARGIN');
        $tax = config('app.TAX');
        $payment_gateway = config('app.PAYMENT_GATEWAY');

        if ($payment_gateway === 0.0) {
            return ceil(($supplier_price * (1 + $margin))
                / (1 - $tax));
        }

        $payment_gateway_configs = [
            1.25 => [
                'percentage' => 0.0125,
                'low_fixed' => 12.5,
                'high_fixed' => 1250,
            ],
            2.5 => [
                'percentage' => 0.025,
                'low_fixed' => 25,
                'high_fixed' => 2500,
            ],
        ];

        $payment_gateway_config = $payment_gateway_configs[$payment_gateway];

        if ($supplier_price <= 1000) {
            return ceil(($supplier_price * (1 + $margin) + $payment_gateway_config['low_fixed'])
                / (1 - $tax));
        }

        if ($supplier_price >= 100000) {
            return ceil(($supplier_price * (1 + $margin) + $payment_gateway_config['high_fixed'])
                / (1 - $tax));
        }

        return ceil(($supplier_price * (1 + $margin))
            / (1 - $tax - $payment_gateway_config['percentage']));
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

            try {
                Log::channel('get_category_price_errors')->error(
                    "\n\n" .
                    "Description: Failed to get category from oneclickdz.\n\n" .
                    "Error message: - .\n\n" .
                    "Category ID: " . $this->requested_category->id . "\n\n" .
                    "Category Name: " . $this->requested_category->name . "\n\n" .
                    "Category Price: " . $this->requested_category->price . "\n\n" .
                    "User ID: " . $this->global_request_object->get('logged_in_user')?->id . "\n\n" .
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
                'Internal server error. Please try again later.',
                500
            );
        }

        $child_categories = $response->json()["data"]["types"];

        foreach ($child_categories as $child_category) {

            if ($child_category["id"] === $oneclickdz_category_id) {
                return $child_category;
            }

        }

        try {
            Log::channel('get_category_price_errors')->error(
                "\n\n" .
                "Description: Category not found at oneclickdz.\n\n" .
                "Error message: - .\n\n" .
                "Category ID: " . $this->requested_category->id . "\n\n" .
                "Category Name: " . $this->requested_category->name . "\n\n" .
                "Category Price: " . $this->requested_category->price . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')?->id . "\n\n" .
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
            'Internal server error. Please try again later.',
            500
        );
    }
    private function checkPriceAtOneClickDZ()
    {
        $oneclickdz_category = $this->getCategoryFromOneClickDz(
            $this->requested_category->oneclickdz_id,
            $this->requested_category->parentCategory->oneclickdz_id
        );

        $received_price_ttc = $this->calculateCustomerPriceTTC(
            $oneclickdz_category["price"]
        );

        if ((double) $this->requested_category->price !== $received_price_ttc) {

            try {

                $old_price = (double) $this->requested_category->price;

                $is_updated = $this->requested_category->update([
                    "price" => $received_price_ttc,
                    "updated_at" => now()
                ]);

                if (!$is_updated) {
                    throw new Exception(
                        "- .",
                        500
                    );
                }

                try {
                    Log::channel('get_category_price_requests')->info(
                        "\n\n" .
                        "Description: Requested category price updated successfully.\n\n" .
                        json_encode([
                            [
                                "passicode_category_id" => $this->requested_category->id,
                                "passicode_category_name" => $this->requested_category->name,
                                "passicode_old_price" => $old_price,
                                "passicode_new_price" => $received_price_ttc,
                                "passicode_category" => $this->requested_category->toArray(),
                                "oneclickdz_category" => $oneclickdz_category,
                            ]
                        ], JSON_PRETTY_PRINT) . "\n\n" .
                        "----------------------------------------------------------------------------------------------------------------------------------\n" .
                        "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                    );
                } catch (Throwable $th) {
                    //throw $th;
                }

            } catch (Throwable $th) {
                try {
                    Log::channel('get_category_price_errors')->error(
                        "\n\n" .
                        "Description: Failed to update requested category price.\n\n" .
                        "Error message: " . $th->getMessage() . "\n\n" .
                        "Category ID: " . $this->requested_category->id . "\n\n" .
                        "Category Name: " . $this->requested_category->name . "\n\n" .
                        "Passicode Category Price: " . (double) $this->requested_category->price . "\n\n" .
                        "Passicode New Category Price: " . $received_price_ttc . "\n\n" .
                        "OneClickDZ Category Price: " . $oneclickdz_category["price"] . "\n\n" .
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
    }


    private function loadRequestedCategory()
    {
        try {

            $this->requested_category = Category::where(
                "id",
                $this->global_request_object->category_id
            )
                ->with("parentCategory")
                ->first();
        } catch (Throwable $th) {
            try {
                Log::channel('get_category_price_errors')->error(
                    "\n\n" .
                    "Description: Failed to get requested category from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Category ID: " . $this->global_request_object->category_id . "\n\n" .
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
                Log::channel('get_category_price_errors')->error(
                    "\n\n" .
                    "Description: Requested category not found.\n\n" .
                    "Error message: - .\n\n" .
                    "Category ID: " . $this->global_request_object->category_id . "\n\n" .
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

    public function __invoke(Request $request)
    {
        $this->global_request_object = $request;

        $this->loadRequestedCategory();

        $this->checkPriceAtOneClickDZ();

        return response()->json([
            'category' => [
                'price' => $this->requested_category->price,
                'discount' => $this->requested_category->discount,
            ],
        ], 200);
    }
}
