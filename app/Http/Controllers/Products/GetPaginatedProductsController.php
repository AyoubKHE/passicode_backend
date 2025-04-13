<?php

namespace App\Http\Controllers\Products;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Models\Products\Product;
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

        if ($limit > 100) {
            throw new Exception(
                'Limit must not exceed 100 to ensure optimal performance.',
                400
            );
        }

        try {
            $this->paginated_products = Product::where('sold', 0)
                ->paginate(perPage: $limit, page: $page);
        } catch (Throwable $th) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
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
