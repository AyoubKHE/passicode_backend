<?php

namespace App\Http\Controllers\Products;

use Exception;
use Throwable;
use App\Models\Products\Product;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\Products\ProductsCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Http\Requests\Products\GetPaginatedProductsByFilterRequest;

class GetPaginatedProductsByFilterController extends Controller
{
    private GetPaginatedProductsByFilterRequest $global_request_object;
    private LengthAwarePaginator $paginated_products;
    private Builder $product_filter_query;
    private array $sent_filter;

    private function prepareUpdatedAt()
    {
        $this->product_filter_query->when(
            array_key_exists('updated_at', $this->sent_filter),
            function (Builder $query) {

                if ($this->sent_filter['updated_at'] === null) {
                    $query->whereNull('updated_at');
                } else {
                    if (array_key_exists("from", $this->sent_filter['updated_at'])) {
                        $min_updated_at = $this->sent_filter['updated_at']['from'];
                    } else {
                        try {
                            $min_updated_at = Product::min("updated_at");
                        } catch (Throwable $th) {

                            Log::channel('get_paginated_products_by_filter_errors')->error(
                                "\n\n" .
                                "Description: Failed to get minimum updated_at of products from database.\n\n" .
                                "Error message: " . $th->getMessage() . "\n\n" .
                                "Page: " . (int) $this->global_request_object->get('page', 1) . "\n\n" .
                                "Limit: " . (int) $this->global_request_object->get('limit', 10) . "\n\n" .
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

                    if (array_key_exists("to", $this->sent_filter['updated_at'])) {
                        $max_updated_at = $this->sent_filter['updated_at']['to'];
                    } else {
                        try {
                            $max_updated_at = Product::max("updated_at");
                        } catch (Throwable $th) {

                            Log::channel('get_paginated_products_by_filter_errors')->error(
                                "\n\n" .
                                "Description: Failed to get maximum updated_at of products from database.\n\n" .
                                "Error message: " . $th->getMessage() . "\n\n" .
                                "Page: " . (int) $this->global_request_object->get('page', 1) . "\n\n" .
                                "Limit: " . (int) $this->global_request_object->get('limit', 10) . "\n\n" .
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

                    $query->whereBetween('updated_at', [$min_updated_at, $max_updated_at]);
                }
            }
        );
    }

    private function prepareCreatedAt()
    {
        $this->product_filter_query->when(
            array_key_exists('created_at', $this->sent_filter),
            function (Builder $query) {
                if (array_key_exists("from", $this->sent_filter['created_at'])) {
                    $min_created_at = $this->sent_filter['created_at']['from'];
                } else {
                    try {
                        $min_created_at = Product::min("created_at");
                    } catch (Throwable $th) {

                        Log::channel('get_paginated_products_by_filter_errors')->error(
                            "\n\n" .
                            "Description: Failed to get minimum created_at of products from database.\n\n" .
                            "Error message: " . $th->getMessage() . "\n\n" .
                            "Page: " . (int) $this->global_request_object->get('page', 1) . "\n\n" .
                            "Limit: " . (int) $this->global_request_object->get('limit', 10) . "\n\n" .
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

                if (array_key_exists("to", $this->sent_filter['created_at'])) {
                    $max_created_at = $this->sent_filter['created_at']['to'];
                } else {
                    try {
                        $max_created_at = Product::max("created_at");
                    } catch (Throwable $th) {

                        Log::channel('get_paginated_products_by_filter_errors')->error(
                            "\n\n" .
                            "Description: Failed to get maximum created_at of products from database.\n\n" .
                            "Error message: " . $th->getMessage() . "\n\n" .
                            "Page: " . (int) $this->global_request_object->get('page', 1) . "\n\n" .
                            "Limit: " . (int) $this->global_request_object->get('limit', 10) . "\n\n" .
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

                $query->whereBetween('created_at', [$min_created_at, $max_created_at]);
            }
        );
    }

    private function prepareSupplier()
    {
        $this->product_filter_query->when(
            array_key_exists('supplier', $this->sent_filter),
            function (Builder $query) {
                $query->where(
                    'supplier',
                    'like',
                    "%" . $this->sent_filter['supplier'] . "%"
                );
            }
        );
    }

    private function preparePurchasePrice()
    {
        $this->product_filter_query->when(
            array_key_exists('purchase_price', $this->sent_filter),
            function (Builder $query) {
                if (array_key_exists("from", $this->sent_filter['purchase_price'])) {
                    $min_purchase_price = $this->sent_filter['purchase_price']['from'];
                } else {
                    try {
                        $min_purchase_price = Product::min("purchase_price");
                    } catch (Throwable $th) {

                        Log::channel('get_paginated_products_by_filter_errors')->error(
                            "\n\n" .
                            "Description: Failed to get minimum purchase_price of products from database.\n\n" .
                            "Error message: " . $th->getMessage() . "\n\n" .
                            "Page: " . (int) $this->global_request_object->get('page', 1) . "\n\n" .
                            "Limit: " . (int) $this->global_request_object->get('limit', 10) . "\n\n" .
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

                if (array_key_exists("to", $this->sent_filter['purchase_price'])) {
                    $max_purchase_price = $this->sent_filter['purchase_price']['to'];
                } else {
                    try {
                        $max_purchase_price = Product::max("purchase_price");
                    } catch (Throwable $th) {

                        Log::channel('get_paginated_products_by_filter_errors')->error(
                            "\n\n" .
                            "Description: Failed to get maximum purchase_price of products from database.\n\n" .
                            "Error message: " . $th->getMessage() . "\n\n" .
                            "Page: " . (int) $this->global_request_object->get('page', 1) . "\n\n" .
                            "Limit: " . (int) $this->global_request_object->get('limit', 10) . "\n\n" .
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

                $query->whereBetween('purchase_price', [$min_purchase_price, $max_purchase_price]);
            }
        );
    }

    private function prepareExpirationDate()
    {
        $this->product_filter_query->when(
            array_key_exists('expiration_date', $this->sent_filter),
            function (Builder $query) {

                if ($this->sent_filter['expiration_date'] === null) {
                    $query->where('expiration_date', "9999-12-31");
                } else {
                    if (array_key_exists("from", $this->sent_filter['expiration_date'])) {
                        $min_expiration_date = $this->sent_filter['expiration_date']['from'];
                    } else {
                        try {
                            $min_expiration_date = Product::min("expiration_date");
                        } catch (Throwable $th) {

                            Log::channel('get_paginated_products_by_filter_errors')->error(
                                "\n\n" .
                                "Description: Failed to get minimum expiration_date of products from database.\n\n" .
                                "Error message: " . $th->getMessage() . "\n\n" .
                                "Page: " . (int) $this->global_request_object->get('page', 1) . "\n\n" .
                                "Limit: " . (int) $this->global_request_object->get('limit', 10) . "\n\n" .
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

                    if (array_key_exists("to", $this->sent_filter['expiration_date'])) {
                        $max_expiration_date = $this->sent_filter['expiration_date']['to'];
                    } else {
                        try {
                            $max_expiration_date = Product::max("expiration_date");
                        } catch (Throwable $th) {

                            Log::channel('get_paginated_products_by_filter_errors')->error(
                                "\n\n" .
                                "Description: Failed to get maximum expiration_date of products from database.\n\n" .
                                "Error message: " . $th->getMessage() . "\n\n" .
                                "Page: " . (int) $this->global_request_object->get('page', 1) . "\n\n" .
                                "Limit: " . (int) $this->global_request_object->get('limit', 10) . "\n\n" .
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

                    $query->whereBetween(
                        'expiration_date',
                        [$min_expiration_date, $max_expiration_date]
                    )->where('expiration_date', '!=', "9999-12-31");
                }
            }
        );
    }

    private function prepareStatus()
    {
        $this->product_filter_query->when(
            array_key_exists('status', $this->sent_filter),
            function (Builder $query): void {
                $query->where('status', $this->sent_filter['status']);
            }
        );
    }

    private function prepareSold()
    {
        $this->product_filter_query->when(
            array_key_exists('sold', $this->sent_filter),
            function (Builder $query): void {
                $query->where('sold', $this->sent_filter['sold']);
            }
        );
    }

    private function prepareRelatedCategory()
    {
        $this->product_filter_query->when(
            array_key_exists('related_category_id', $this->sent_filter),
            function (Builder $query) {
                $query->where(
                    'category_id',
                    $this->sent_filter['related_category_id']
                );
            }
        );
    }

    private function prepareCode()
    {
        $this->product_filter_query->when(
            array_key_exists('code', $this->sent_filter),
            function (Builder $query) {
                $query->where(
                    'code_start',
                    'like',
                    $this->sent_filter['code'] . "%"
                );
            }
        );
    }

    private function prepareId()
    {
        $this->product_filter_query->when(
            array_key_exists('id', $this->sent_filter),
            function (Builder $query) {
                $query->where(
                    'id',
                    'like',
                    "%" . $this->sent_filter['id'] . "%"
                );
            }
        );
    }

    private function preparePaginatedProducts()
    {
        $page = (int) $this->global_request_object->get('page', 1);
        $limit = (int) $this->global_request_object->get('limit', 10);

        if ($limit > 100) {
            try {
                Log::channel('get_paginated_products_by_filter_errors')->error(
                    "\n\n" .
                    "Description: Limit must not exceed 100 to ensure optimal performance.\n\n" .
                    "Error message: - .\n\n" .
                    "Page: " . $page . "\n\n" .
                    "Limit: " . $limit . "\n\n" .
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
                'Limit must not exceed 100 to ensure optimal performance.',
                400
            );
        }

        $this->sent_filter = $this->global_request_object->validated();

        $this->product_filter_query = Product::query();

        $this->prepareId();

        $this->prepareCode();

        $this->prepareRelatedCategory();

        $this->prepareSold();

        $this->prepareStatus();

        $this->prepareExpirationDate();

        $this->preparePurchasePrice();

        $this->prepareSupplier();

        $this->prepareCreatedAt();

        $this->prepareUpdatedAt();

        // $sql = $this->product_filter_query->toRawSql();

        try {
            $this->paginated_products = $this->product_filter_query
                ->paginate(perPage: $limit, page: $page);
        } catch (Throwable $th) {
            try {
                Log::channel('get_paginated_products_by_filter_errors')->error(
                    "\n\n" .
                    "Description: Failed to get paginated products by filter from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Page: " . $page . "\n\n" .
                    "Limit: " . $limit . "\n\n" .
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
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

    }

    public function __invoke(GetPaginatedProductsByFilterRequest $request)
    {
        $this->global_request_object = $request;

        $this->preparePaginatedProducts();

        if (count($this->paginated_products) > 0) {
            return response()->json([
                'products_data' => new ProductsCollection($this->paginated_products),
            ], 200);
        } else {
            return response()->json([
                'products_data' => [
                    'products' => [],
                    'meta' => null
                ],
            ], 200);
        }
    }
}
