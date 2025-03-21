<?php

namespace App\Http\Controllers\Categories;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Products\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Http\Resources\Products\CategoriesCollection;


class GetPaginatedCategoriesController extends Controller
{
    private Request $global_request_object;
    private LengthAwarePaginator $paginated_categories;

    private function preparePaginatedCategories()
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
            $this->paginated_categories = Category::paginate(perPage: $limit, page: $page);
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

        $this->preparePaginatedCategories();

        if (count($this->paginated_categories) > 0) {
            return response()->json([
                'categories_data' => new CategoriesCollection($this->paginated_categories),
            ], 200);
        } else {
            return response()->json([
                'categories_data' => [
                    'categories' => [],
                    'meta' => null
                ],
            ], 200);
        }
    }
}
