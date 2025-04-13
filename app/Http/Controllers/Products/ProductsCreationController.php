<?php

namespace App\Http\Controllers\Products;

use App\Models\Products\Product;
use Exception;
use Throwable;
use Illuminate\Support\Arr;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use App\Models\Products\Product_Category;
use App\Http\Requests\Products\ProductsCreationRequest;


class ProductsCreationController extends Controller
{
    private ProductsCreationRequest $global_request_object;
    private array $prepared_products;
    private int $related_category_id;

    private function storeProducts(): void
    {
        foreach ($this->prepared_products as $prepared_product) {

            $prepared_product['code'] = Crypt::encryptString($prepared_product['code']);

            $prepared_product['sold'] = false;

            $prepared_product['created_at'] = now();

            $prepared_product['updated_at'] = null;

            try {
                $stored_product = Product::create($prepared_product);
            } catch (Throwable $throwable) {
                throw new Exception(
                    'An error occurred while accessing the database. Please try again later.',
                    500
                );
            }

            if (!$stored_product) {
                throw new Exception(
                    'An error occurred while accessing the database. Please try again later.',
                    500
                );
            }

            try {
                $is_inserted = Product_Category::insert([
                    'product_id' => $stored_product->id,
                    'category_id' => $this->related_category_id
                ]);
            } catch (Throwable $th) {
                throw new Exception(
                    'An error occurred while accessing the database. Please try again later.',
                    500
                );
            }

            if (!$is_inserted) {
                throw new Exception(
                    'An error occurred while accessing the database. Please try again later.',
                    500
                );
            }
        }
    }

    private function preparingData()
    {
        $sent_inputs = $this->global_request_object->validated();

        $this->prepared_products = Arr::only($sent_inputs, 'products')['products'];
        $this->related_category_id = Arr::only($sent_inputs, 'related_category_id')['related_category_id'];
    }

    public function __invoke(ProductsCreationRequest $request): JsonResponse
    {
        $this->global_request_object = $request;

        $this->preparingData();

        DB::transaction(function () {
            $this->storeProducts();
        });

        return response()->json([
            'message' => 'Products created successfully.',
        ], 201);
    }
}
