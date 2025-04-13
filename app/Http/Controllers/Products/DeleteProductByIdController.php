<?php

namespace App\Http\Controllers\Products;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Products\Product;

class DeleteProductByIdController extends Controller
{
    private Request $global_request_object;
    private Product|null $requested_product;

    private function deleteRequestedProduct()
    {
        try {
            $is_deleted = $this->requested_product->delete();
        } catch (Throwable $throwable) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$is_deleted) {
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
            )->first();
        } catch (Throwable $th) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$this->requested_product) {
            throw new Exception('Requested product not found.', 404);
        }
    }

    public function __invoke(Request $request)
    {
        $this->global_request_object = $request;

        $this->loadRequestedProduct();

        $this->deleteRequestedProduct();

        return response()->json([
            'message' => 'Product deleted successfully.',
        ], 200);

    }
}
