<?php

namespace App\Http\Controllers\Categories;

use Exception;
use Illuminate\Http\Request;
use App\Models\Products\Category;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;


class OneClickDzCategoriesValidationController extends Controller
{
    private array $oneclickdz_catalog;
    private array $oneclickdz_categories;

    private array $validation_results;


    private function getChildCategoriesFromOneClickDz(string $oneclickdz_category_id)
    {
        $response = Http::withHeaders([
            'X-Access-Token' => config('app.ONECLICKDZ_API_TOKEN_TEST')
        ])->get("https://api.oneclickdz.com/v3/gift-cards/checkProduct/" . $oneclickdz_category_id);

        if ($response->failed()) {
            throw new Exception(
                'Invalid oneclickdz api token.',
                401
            );
        }

        return $response->json()["data"]["types"];
    }


    private function buildOneClickDzCategories()
    {
        $this->oneclickdz_categories = [];

        foreach ($this->oneclickdz_catalog["data"]["categories"] as $oneclickdz_category) {

            if ($oneclickdz_category["title"] === "Mobile & Internet") {
                continue;
            }

            foreach ($oneclickdz_category["products"] as $oneclickdz_product) {

                $this->oneclickdz_categories[$oneclickdz_product["id"]] = $oneclickdz_product["title"];

                $child_categories = $this->getChildCategoriesFromOneClickDz(
                    $oneclickdz_product["id"]
                );

                foreach ($child_categories as $child_category) {

                    $this->oneclickdz_categories[$child_category["id"]] =
                        $oneclickdz_product["title"] . " " . $child_category["name"];

                }
            }
        }
    }


    private function getCatalogFromOneClickDz()
    {
        $response = Http::withHeaders([
            'X-Access-Token' => config('app.ONECLICKDZ_API_TOKEN_TEST')
        ])->get("https://api.oneclickdz.com/v3/gift-cards/catalog");

        if ($response->failed()) {
            throw new Exception(
                'Invalid oneclickdz api token.',
                401
            );
        }

        $this->oneclickdz_catalog = $response->json();

    }


    private function validateWithPassicodeCategories()
    {

        $this->validation_results = [];
        $this->validation_results["found_in_both"] = [];
        $this->validation_results["not_found_in_passicode"] = [];
        $this->validation_results["not_found_in_oneclickdz"] = [];

        $passicode_categories = Category::pluck('name', 'oneclickdz_id')->toArray();

        foreach ($this->oneclickdz_categories as $oneclickdz_category_id => $oneclickdz_category_name) {

            if (isset($passicode_categories[$oneclickdz_category_id])) {
                $passicode_category = $passicode_categories[$oneclickdz_category_id];

                array_push(
                    $this->validation_results["found_in_both"],
                    [
                        "passicode_name" => $passicode_category,
                        "oneclickdz_name" => $oneclickdz_category_name,
                    ]
                );
            } else {
                array_push(
                    $this->validation_results["not_found_in_passicode"],
                    [
                        "oneclickdz_id" => $oneclickdz_category_id,
                        "oneclickdz_name" => $oneclickdz_category_name,
                    ]
                );
            }
        }

        $passicode_categories = Category::all();

        foreach ($passicode_categories as $passicode_category) {

            if ($passicode_category->oneclickdz_id) {
                if (!isset($this->oneclickdz_categories[$passicode_category->oneclickdz_id])) {
                    array_push(
                        $this->validation_results["not_found_in_oneclickdz"],
                        [
                            "passicode_id" => $passicode_category->id,
                            "passicode_oneclickdz_id" => $passicode_category->oneclickdz_id,
                            "passicode_name" => $passicode_category->name,
                        ]
                    );
                }
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

        $this->buildOneClickDzCategories();

        $this->validateWithPassicodeCategories();

        return response()->json([
            "message" => "validation finished.",
            "validation_results" => [
                [
                    "found_in_both" => [
                        "quantity" => count($this->validation_results["found_in_both"]),
                        "categories" => $this->validation_results["found_in_both"]
                    ],
                ],
                [
                    "not_found_in_passicode" => [
                        "quantity" => count($this->validation_results["not_found_in_passicode"]),
                        "categories" => $this->validation_results["not_found_in_passicode"]
                    ],
                ],
                [
                    "not_found_in_oneclickdz" => [
                        "quantity" => count($this->validation_results["not_found_in_oneclickdz"]),
                        "categories" => $this->validation_results["not_found_in_oneclickdz"]
                    ],
                ]
            ]
        ], 200);
    }
}
