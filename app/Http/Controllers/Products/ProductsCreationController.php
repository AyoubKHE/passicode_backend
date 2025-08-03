<?php

namespace App\Http\Controllers\Products;

use Exception;
use Throwable;
use Illuminate\Support\Arr;
use App\Models\Products\Product;
use App\Models\Products\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\Products\ProductsCreationRequest;


class ProductsCreationController extends Controller
{
    private ProductsCreationRequest $global_request_object;
    private array $prepared_products;
    private array $stored_products;
    private int $related_category_id;
    private string $supplier;

    private function updateCategoryQuantity()
    {
        try {

            $related_category = Category::where(
                "id",
                $this->related_category_id
            )
                ->lockForUpdate()
                ->first();
        } catch (Throwable $th) {

            Log::channel('products_creation_errors')->error(
                "\n\n" .
                "Description: Failed to get related category from database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Category ID: " . $this->related_category_id . "\n\n" .
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

        $related_category->quantity += count($this->prepared_products);

        $related_category->updated_at = now();

        try {
            $is_updated = $related_category->save();

            if (!$is_updated) {
                throw new Exception(
                    "- .",
                    500
                );
            }
        } catch (Throwable $th) {

            Log::channel('products_creation_errors')->error(
                "\n\n" .
                "Description: Failed to update category's quantity in database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Category ID: " . $this->related_category_id . "\n\n" .
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

    private function storeProducts(): void
    {

        $this->stored_products = [];

        foreach ($this->prepared_products as $prepared_product) {


            $prepared_product['category_id'] = $this->related_category_id;

            $prepared_product['code_start'] = substr($prepared_product['code'], 0, 5);

            $prepared_product['code'] = Crypt::encryptString($prepared_product['code']);

            $prepared_product['sold'] = false;

            $prepared_product['status'] = "valid";

            if (!$prepared_product['expiration_date']) {
                $prepared_product['expiration_date'] = "9999-12-31";
            }

            $prepared_product['supplier'] = $this->supplier;

            $prepared_product['created_at'] = now();

            $prepared_product['updated_at'] = null;

            try {
                $stored_product = Product::create($prepared_product);

                if (!$stored_product) {
                    throw new Exception(
                        "- .",
                        500
                    );
                }

                array_push($this->stored_products, $stored_product);
            } catch (Throwable $th) {

                Log::channel('products_creation_errors')->error(
                    "\n\n" .
                    "Description: Failed to store product in database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Category ID: " . $this->related_category_id . "\n\n" .
                    "Code Start: " . $prepared_product['code_start'] . "\n\n" .
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

    private function preparingData()
    {
        $sent_inputs = $this->global_request_object->validated();

        $this->prepared_products = Arr::only($sent_inputs, 'products')['products'];
        $this->related_category_id = Arr::only($sent_inputs, 'related_category_id')['related_category_id'];
        $this->supplier = Arr::only($sent_inputs, 'supplier')['supplier'];
    }

    private function logRequest()
    {
        try {

            Log::channel('products_creation_requests')->info(
                "\n\n" .
                "Description: Products created successfully.\n\n" .
                "Products Data: \n" .
                json_encode($this->stored_products, JSON_PRETTY_PRINT) . "\n\n" .
                "Category ID: " . $this->related_category_id . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );
        } catch (Throwable $th) {
            //throw $th;
        }

    }

    public function __invoke(ProductsCreationRequest $request): JsonResponse
    {
        $this->global_request_object = $request;

        $this->preparingData();

        DB::transaction(function () {
            $this->storeProducts();

            $this->updateCategoryQuantity();
        });

        $this->logRequest();

        return response()->json([
            'message' => 'Products created successfully.',
        ], 201);
    }
}
