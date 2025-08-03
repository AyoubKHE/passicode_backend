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

class getChildCategoriesController extends Controller
{
    private Request $global_request_object;
    private Collection $categories;

    private function loadChildCategories()
    {
        try {

            $this->categories = Category::where(
                "parent_id",
                $this->global_request_object->category_id
            )
                ->orderBy('id', 'asc')
                ->get();
        } catch (Throwable $th) {

            try {
                Log::channel('get_child_categories_requests')->info(
                    "\n\n" .
                    "Description: User attempted to search for a category by id, but an error occurred while accessing the database.\n\n" .
                    "Requested Category Id: << " . $this->global_request_object->category_id . " >>\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );
            } catch (Throwable $th) {
                //throw $th;
            }

            Log::channel('get_child_categories_errors')->error(
                "\n\n" .
                "Description: Failed to get child categories from database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Requested Category Id: << " . $this->global_request_object->category_id . " >>\n\n" .
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
            Log::channel('get_child_categories_requests')->info(
                "\n\n" .
                "Description: The user attempted to search for a category by id, but the count of child categories = 0.\n\n" .
                "Requested Category Id: << " . $this->global_request_object->category_id . " >>\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );
        } else {
            Log::channel('get_child_categories_requests')->info(
                "\n\n" .
                "Description: A user searched for category by id.\n\n" .
                "Requested Category Id: << " . $this->global_request_object->category_id . " >>\n\n" .
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

    public function __invoke(Request $request)
    {
        $this->global_request_object = $request;

        $this->loadChildCategories();

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
