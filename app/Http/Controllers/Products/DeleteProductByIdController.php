<?php

namespace App\Http\Controllers\Products;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Models\Products\Product;
use App\Models\Products\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class DeleteProductByIdController extends Controller
{
    private Request $global_request_object;
    private Product|null $requested_product;

    private function updateRelatedCategoryQuantity()
    {

        try {
            $related_category = Category::where(
                "id",
                $this->requested_product->category_id
            )
                ->lockForUpdate()
                ->first();
        } catch (Throwable $th) {
            Log::channel('delete_product_by_id_errors')->error(
                "\n\n" .
                "Description: Failed to get related category from database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
                "Category ID: " . $this->requested_product->category_id . "\n\n" .
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

        if (!$related_category) {

            Log::channel('delete_product_by_id_errors')->error(
                "\n\n" .
                "Description: Related Category not found.\n\n" .
                "Error message: - .\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
                "Category ID: " . $this->requested_product->category_id . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'Related Category not found.',
                404
            );
        }

        $related_category->quantity -= 1;
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

            Log::channel('delete_product_by_id_errors')->error(
                "\n\n" .
                "Description: Failed to update related category's quantity in database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
                "Category ID: " . $this->requested_product->category_id . "\n\n" .
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

    private function deleteRequestedProduct()
    {
        try {

            $is_deleted = $this->requested_product->delete();

            if (!$is_deleted) {
                throw new Exception(
                    "- .",
                    500
                );
            }
        } catch (Throwable $th) {

            Log::channel('delete_product_by_id_errors')->error(
                "\n\n" .
                "Description: Failed to delete requested product in database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
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

    private function loadRequestedProduct()
    {
        try {

            $this->requested_product = Product::where(
                "id",
                $this->global_request_object->product_id
            )
                ->lockForUpdate()
                ->first();
        } catch (Throwable $th) {

            Log::channel('delete_product_by_id_errors')->error(
                "\n\n" .
                "Description: Failed to get requested product from database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
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

        if (!$this->requested_product) {

            Log::channel('delete_product_by_id_errors')->error(
                "\n\n" .
                "Description: Requested product not found.\n\n" .
                "Error message: - .\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'Requested product not found.',
                404
            );
        }
    }

    private function logRequest()
    {
        try {

            Log::channel('delete_product_by_id_requests')->info(
                "\n\n" .
                "Description: Product deleted successfully.\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
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

    public function __invoke(Request $request)
    {
        $this->global_request_object = $request;

        DB::transaction(function () {

            $this->loadRequestedProduct();

            $this->updateRelatedCategoryQuantity();

            $this->deleteRequestedProduct();
        });

        $this->logRequest();

        return response()->json([
            'message' => 'Product deleted successfully.',
        ], 200);

    }
}
