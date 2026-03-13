<?php

namespace App\Http\Controllers\Categories;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Models\Products\Category;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Resources\Products\AllCategoriesCollection;

class getChildCategoriesByNameController extends Controller
{
    private Request $global_request_object;
    private Collection $categories;

    private function loadCategories()
    {
        try {

            $category = Category::where(
                'name',
                'like',
                $this->global_request_object->category_name . '%'
            )
                ->where("is_active", 1)
                ->first();

        } catch (Throwable $th) {

            try {
                Log::channel('get_child_categories_by_name_requests')->info(
                    "\n\n" .
                    "Description: User attempted to search for a category by name, but an error occurred while accessing the database.\n\n" .
                    "Requested Category Name: << " . $this->global_request_object->category_name . " >>\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );
            } catch (Throwable $th) {
                //throw $th;
            }
            try {
                Log::channel('get_child_categories_by_name_errors')->error(
                    "\n\n" .
                    "Description: Failed to get requested category by name from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Requested Category Name: << " . $this->global_request_object->category_name . " >>\n\n" .
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

        if (!$category) {

            try {
                Log::channel('get_child_categories_by_name_requests')->info(
                    "\n\n" .
                    "Description: User attempted to search for a category by name, but the requested category is not found.\n\n" .
                    "Requested Category Name: << " . $this->global_request_object->category_name . " >>\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );
            } catch (Throwable $th) {
                //throw $th;
            }

            try {
                Log::channel('get_child_categories_by_name_errors')->error(
                    "\n\n" .
                    "Description: Requested category not found.\n\n" .
                    "Error message: - .\n\n" .
                    "Requested Category Name: << " . $this->global_request_object->category_name . " >>\n\n" .
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
                'Requested category not found.',
                404
            );
        }

        if ($category->is_leaf_category) {
            $this->categories = new Collection([$category]);
            try {
                Log::channel('get_child_categories_by_name_requests')->info(
                    "\n\n" .
                    "Description: A user searched for category by name.\n\n" .
                    "Requested Category Name: << " . $this->global_request_object->category_name . " >>\n\n" .
                    "Returned Category: << " . $category->name . " >>\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );
            } catch (Throwable $th) {
                //throw $th;
            }

        } else {
            try {

                $this->categories = Category::where(
                    "parent_id",
                    $category->id
                )
                    ->where("is_active", 1)
                    ->orderBy('price', 'asc')
                    ->get();
            } catch (Throwable $th) {

                try {
                    Log::channel('get_child_categories_by_name_requests')->info(
                        "\n\n" .
                        "Description: User attempted to search for a category by name, but an error occurred while accessing the database.\n\n" .
                        "Requested Category Name: << " . $this->global_request_object->category_name . " >>\n\n" .
                        "Ip: " . $this->global_request_object->ip() . "\n\n" .
                        "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                        "----------------------------------------------------------------------------------------------------------------------------------\n" .
                        "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                    );
                } catch (Throwable $th) {
                    //throw $th;
                }

                Log::channel('get_child_categories_by_name_errors')->error(
                    "\n\n" .
                    "Description: Failed to get child categories from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Requested Category Name: << " . $this->global_request_object->category_name . " >>\n\n" .
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

            if ($this->categories->count() === 0) {
                Log::channel('get_child_categories_by_name_requests')->info(
                    "\n\n" .
                    "Description: The user attempted to search for a category by name, but the count of child categories = 0.\n\n" .
                    "Requested Category Name: << " . $this->global_request_object->category_name . " >>\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );
            } else {
                Log::channel('get_child_categories_by_name_requests')->info(
                    "\n\n" .
                    "Description: A user searched for category by name.\n\n" .
                    "Requested Category Name: << " . $this->global_request_object->category_name . " >>\n" .
                    "Parent Category Name: << " . $category->name . " >>\n" .
                    "Child Categories Count: " . $this->categories->count() . "\n" .
                    "Returned Categories Names: [ " .
                    $this->categories->map(function ($category) {
                        return $category->name;
                    })->implode(', ') . " ]\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );
            }
        }
    }

    public function __invoke(Request $request)
    {
        $this->global_request_object = $request;

        $this->loadCategories();

        if ($this->categories) {
            return response()->json([
                'data' => new AllCategoriesCollection($this->categories),
            ], 200);
        } else {
            return response()->json([
                'data' => [],
            ], 200);
        }
    }
}
