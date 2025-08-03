<?php

namespace App\Http\Controllers\Products;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Models\Products\Product;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\Products\ProductsCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;


class GetPaginatedProductsController extends Controller
{
    private Request $global_request_object;
    private LengthAwarePaginator $paginated_products;

    private function preparePaginatedProducts()
    {
        $page = (int) $this->global_request_object->get('page', 1);
        $limit = (int) $this->global_request_object->get('limit', 10);
        $code = $this->global_request_object->get('code', "");

        if ($limit > 100) {

            Log::channel('get_paginated_products_errors')->error(
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

            throw new Exception(
                'Limit must not exceed 100 to ensure optimal performance.',
                400
            );
        }
        if ($code) {
            try {
                $this->paginated_products = Product::where(
                    'code_start',
                    'like',
                    $code . "%"
                )
                    ->paginate(perPage: $limit, page: $page);
            } catch (Throwable $th) {

                Log::channel('get_paginated_products_errors')->error(
                    "\n\n" .
                    "Description: Failed to get paginated products by code from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Page: " . $page . "\n\n" .
                    "Limit: " . $limit . "\n\n" .
                    "Code: " . $code . "\n\n" .
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
        } else {
            try {
                $this->paginated_products = Product::paginate(perPage: $limit, page: $page);
            } catch (Throwable $th) {

                Log::channel('get_paginated_products_errors')->error(
                    "\n\n" .
                    "Description: Failed to get paginated products from database.\n\n" .
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

                throw new Exception(
                    'An error occurred while accessing the database. Please try again later.',
                    500
                );
            }
        }

        // $this->paginated_products = Cache::rememberForever(
        //     "products_page:{$page}_limit:{$limit}",
        //     function () use ($page, $limit) {
        //         try {
        //             $paginated_products = Product::paginate(perPage: $limit, page: $page);
        //         } catch (Throwable $th) {
        //             throw new Exception(
        //                 'An error occurred while accessing the database. Please try again later.',
        //                 500
        //             );
        //         }
        //         return $paginated_products;
        //     }
        // );
    }

    public function __invoke(Request $request)
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
