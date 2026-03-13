<?php

namespace App\Http\Controllers\Categories;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Models\Products\Category;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;


class OneClickDzCategoriesSyncController extends Controller
{
    private array $oneclickdz_catalog;

    private array $sync_results;

    private float $margin;
    private float $tax;
    private float $payment_gateway;

    private function calculateCustomerPriceTTC(float $supplier_price): float
    {
        if ($this->payment_gateway === 0.0) {
            return ceil(($supplier_price * (1 + $this->margin))
                / (1 - $this->tax));
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

        $payment_gateway_config = $payment_gateway_configs[$this->payment_gateway];

        if ($supplier_price <= 1000) {
            return ceil(($supplier_price * (1 + $this->margin) + $payment_gateway_config['low_fixed'])
                / (1 - $this->tax));
        }

        if ($supplier_price >= 100000) {
            return ceil(($supplier_price * (1 + $this->margin) + $payment_gateway_config['high_fixed'])
                / (1 - $this->tax));
        }

        return ceil(($supplier_price * (1 + $this->margin))
            / (1 - $this->tax - $payment_gateway_config['percentage']));
    }


    private function manageLeafCategoryPrice(
        Category $passicode_category,
        array $oneclickdz_category,
        array $oneclickdz_parent_category
    ) {

        $received_price_ttc = $this->calculateCustomerPriceTTC(
            $oneclickdz_category["price"]
        );

        if ((double) $passicode_category->price !== $received_price_ttc) {

            try {
                $old_price = (double) $passicode_category->price;

                $is_updated = $passicode_category->update([
                    "price" => $received_price_ttc,
                    "is_active" => $received_price_ttc == 0 ? 0 : 1,
                    "updated_at" => now()
                ]);

                if (!$is_updated) {
                    throw new Exception(
                        "- .",
                        500
                    );
                }

                array_push($this->sync_results["price_changes"], [
                    "passicode_category_id" => $passicode_category->id,
                    "passicode_category_name" => $passicode_category->name,
                    "passicode_old_price" => $old_price,
                    "passicode_new_price" => $received_price_ttc,
                    "passicode_category" => $passicode_category->toArray(),
                    "oneclickdz_category" => $oneclickdz_category,
                    "oneclickdz_parent_category" => $oneclickdz_parent_category,
                ]);
            } catch (Throwable $th) {
                array_push($this->sync_results["errors"], [
                    "message" => $th->getMessage(),
                    "passicode_category" => $passicode_category->toArray(),
                    "oneclickdz_category" => $oneclickdz_category,
                    "oneclickdz_parent_category" => $oneclickdz_parent_category,
                ]);
            }
        }
    }


    private function syncCategory(
        array $oneclickdz_category,
        array $oneclickdz_parent_category
    ) {

        $passicode_category = Category::where(
            "oneclickdz_id",
            $oneclickdz_category["id"]
        )
            ->first();

        if ($passicode_category) {
            if ($passicode_category->is_leaf_category) {
                $this->manageLeafCategoryPrice(
                    $passicode_category,
                    $oneclickdz_category,
                    $oneclickdz_parent_category
                );
            }
        }
    }


    private function getChildCategoriesFromOneClickDz(string $oneclickdz_category_id)
    {
        $response = Http::withHeaders([
            'X-Access-Token' => config('app.ONECLICKDZ_API_TOKEN')
        ])->get("https://api.oneclickdz.com/v3/gift-cards/checkProduct/" . $oneclickdz_category_id);

        if ($response->failed()) {
            throw new Exception(
                'Invalid oneclickdz api token.',
                401
            );
        }

        return $response->json()["data"]["types"];
    }


    private function syncCategories()
    {

        $this->margin = config('app.MARGIN');
        $this->tax = config('app.TAX');
        $this->payment_gateway = config('app.PAYMENT_GATEWAY');

        $this->sync_results = [];
        $this->sync_results["price_changes"] = [];
        $this->sync_results["errors"] = [];

        foreach ($this->oneclickdz_catalog["data"]["categories"] as $oneclickdz_category) {

            if ($oneclickdz_category["title"] === "Mobile & Internet") {
                continue;
            }

            foreach ($oneclickdz_category["products"] as $oneclickdz_product) {

                $child_categories = $this->getChildCategoriesFromOneClickDz(
                    $oneclickdz_product["id"]
                );

                foreach ($child_categories as $child_category) {

                    $this->syncCategory(
                        $child_category,
                        $oneclickdz_product
                    );

                }
            }
        }
    }


    private function getCatalogFromOneClickDz()
    {
        $response = Http::withHeaders([
            'X-Access-Token' => config('app.ONECLICKDZ_API_TOKEN')
        ])->get("https://api.oneclickdz.com/v3/gift-cards/catalog");

        if ($response->failed()) {
            throw new Exception(
                'Invalid oneclickdz api token.',
                401
            );
        }

        $this->oneclickdz_catalog = $response->json();

    }

    private function logSyncResults()
    {

        if (count($this->sync_results["price_changes"]) > 0) {
            try {

                Log::channel('sync_with_oneclickdz_requests')->info(
                    "\n\n" .
                    "Description: Sync with oneclickdz prices changes results.\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );
            } catch (Throwable $th) {
                //throw $th;
            }
        }
        foreach ($this->sync_results["price_changes"] as $price_change) {
            try {

                Log::channel('sync_with_oneclickdz_requests')->info(
                    json_encode($price_change, JSON_PRETTY_PRINT) . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );
            } catch (Throwable $th) {
                //throw $th;
            }
        }



        if (count($this->sync_results["errors"]) > 0) {
            try {

                Log::channel('sync_with_oneclickdz_errors')->error(
                    "\n\n" .
                    "Description: Sync with oneclickdz prices errors.\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );
            } catch (Throwable $th) {
                //throw $th;
            }
        }
        foreach ($this->sync_results["errors"] as $error) {
            try {

                Log::channel('sync_with_oneclickdz_errors')->error(
                    json_encode($error, JSON_PRETTY_PRINT) . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );
            } catch (Throwable $th) {
                //throw $th;
            }
        }
    }

    public function __invoke(Request $request)
    {

        if ($request->token !== config("app.ONECLICKDZ_SYNC_TOKEN")) {
            throw new Exception(
                'Authorization token is invalid.',
                401
            );
        }

        ini_set("max_execution_time", 600);

        $this->getCatalogFromOneClickDz();

        $this->syncCategories();

        $this->logSyncResults();

        return response()->json([
            "message" => "Sync finished.",
            "sync_results" => [
                [
                    "price_changes" => [
                        "quantity" => count($this->sync_results["price_changes"]),
                        "price_changes" => $this->sync_results["price_changes"]
                    ],
                ],
                [
                    "errors" => [
                        "quantity" => count($this->sync_results["errors"]),
                        "errors" => $this->sync_results["errors"]
                    ],
                ],
            ]
        ], 200);
    }
}
