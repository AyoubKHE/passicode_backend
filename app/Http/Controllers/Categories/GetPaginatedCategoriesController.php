<?php

namespace App\Http\Controllers\Categories;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Models\Products\Category;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
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

            Log::channel('get_paginated_categories_errors')->error(
                "\n\n" .
                "Description: Limit must not exceed 100 to ensure optimal performance.\n\n" .
                "Error message: - .\n\n" .
                "Page: " . $page . "\n\n" .
                "Limit: " . $limit . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

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
                    ->orderBy(
                        'id',
                        'asc'
                    )
                    ->paginate(perPage: $limit, page: $page);
            } catch (Throwable $th) {

                Log::channel('get_paginated_categories_errors')->error(
                    "\n\n" .
                    "Description: Failed to get paginated categories by name from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Page: " . $page . "\n\n" .
                    "Limit: " . $limit . "\n\n" .
                    "Category Name: " . $name . "\n\n" .
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
        } else {
            try {
                $this->paginated_categories = Category::orderBy(
                    'id',
                    'asc'
                )
                    ->paginate(perPage: $limit, page: $page);
            } catch (Throwable $th) {

                Log::channel('get_paginated_categories_errors')->error(
                    "\n\n" .
                    "Description: Failed to get paginated categories from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Page: " . $page . "\n\n" .
                    "Limit: " . $limit . "\n\n" .
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
