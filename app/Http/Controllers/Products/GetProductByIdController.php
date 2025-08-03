<?php

namespace App\Http\Controllers\Products;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Models\Products\Product;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\Products\ProductResource;

class GetProductByIdController extends Controller
{
    private Request $global_request_object;
    private Product|null $requested_product;

    private function loadRequestedProduct()
    {
        try {
            $this->requested_product = Product::where(
                "id",
                $this->global_request_object->product_id
            )
                ->with(['category'])
                ->first();
        } catch (Throwable $th) {

            Log::channel('get_product_by_id_errors')->error(
                "\n\n" .
                "Description: Failed to get requested product from database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
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

    public function __invoke(Request $request)
    {
        $this->global_request_object = $request;

        $this->loadRequestedProduct();

        if ($this->requested_product) {
            return response()->json([
                'product' => new ProductResource($this->requested_product),
            ], 200);
        } else {
            return response()->json([
                'product' => null,
            ], 200);
        }
    }
}
