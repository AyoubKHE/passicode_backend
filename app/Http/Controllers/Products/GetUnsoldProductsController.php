<?php

namespace App\Http\Controllers\Products;

use Exception;
use Throwable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Products\Product;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;


class GetUnsoldProductsController extends Controller
{
    private Request $global_request_object;
    private array $unsold_products;

    private function prepareUnsoldProducts()
    {
        try {
            $temp_unsold_products =
                Product::select('id', 'code')
                    ->where(
                        "category_id",
                        $this->global_request_object->category_id
                    )
                    ->where('sold', false)
                    ->where('status', 'valid')
                    ->where('expiration_date', '>=', Carbon::now()->toDateString())
                    ->orderBy('expiration_date', 'asc')
                    ->orderBy('purchase_price', 'asc')
                    ->get()
                    ->toArray();
        } catch (Throwable $th) {
            try {
                Log::channel('get_unsold_products_errors')->error(
                    "\n\n" .
                    "Description: Failed to get unsold products from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Category ID: " . $this->global_request_object->category_id . "\n\n" .
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

        $this->unsold_products = array_map(function ($unsold_product) {
            return [
                'id' => $unsold_product['id'],
                'code' => Crypt::decryptString($unsold_product['code']),
            ];
        }, $temp_unsold_products);
    }

    public function __invoke(Request $request)
    {
        $this->global_request_object = $request;

        $this->prepareUnsoldProducts();

        return response()->json([
            'unsold_products' => $this->unsold_products,
        ], 200);
    }
}
