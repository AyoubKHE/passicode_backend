<?php

namespace App\Http\Controllers\Products;

use Exception;
use Throwable;
use App\Models\Products\Product;
use App\Http\Resources\Products\ProductResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
                ->with(['categories'])
                ->first();
        } catch (Throwable $th) {
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
