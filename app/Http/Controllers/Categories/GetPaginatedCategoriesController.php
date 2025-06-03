<?php

namespace App\Http\Controllers\Categories;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Models\Products\Category;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\Products\CategoriesCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;


class GetPaginatedCategoriesController extends Controller
{
    private Request $global_request_object;
    private LengthAwarePaginator $paginated_categories;

    private function preparePaginatedCategories()
    {
        $page = (int) $this->global_request_object->get('page', 1);
        $limit = (int) $this->global_request_object->get('limit', 10);
        $name = $this->global_request_object->get('name', "");

        if ($limit > 100) {
            throw new Exception(
                'Limit must not exceed 100 to ensure optimal performance.',
                400
            );
        }

        if ($name) {
            try {
                $this->paginated_categories = Category::where(
                    'name',
                    'like',
                    $name . "%"
                )
                    ->paginate(perPage: $limit, page: $page);
            } catch (Throwable $th) {
                throw new Exception(
                    'An error occurred while accessing the database. Please try again later.',
                    500
                );
            }
        } else {
            try {
                $this->paginated_categories = Category::paginate(perPage: $limit, page: $page);
            } catch (Throwable $th) {
                throw new Exception(
                    'An error occurred while accessing the database. Please try again later.',
                    500
                );
            }
        }

        // $this->paginated_categories = Cache::rememberForever(
        //     "categories_page:{$page}_limit:{$limit}",
        //     function () use ($page, $limit) {
        //         try {
        //             $paginated_categories = Category::paginate(perPage: $limit, page: $page);
        //         } catch (Throwable $th) {
        //             throw new Exception(
        //                 'An error occurred while accessing the database. Please try again later.',
        //                 500
        //             );
        //         }
        //         return $paginated_categories;
        //     }
        // );
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
