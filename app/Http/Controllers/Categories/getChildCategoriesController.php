<?php

namespace App\Http\Controllers\Categories;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Models\Products\Category;
use App\Http\Controllers\Controller;
use App\Http\Resources\Products\AllCategoriesCollection;
use Illuminate\Database\Eloquent\Collection;

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
                ->where(
                    "is_active",
                    1
                )
                ->orderBy('id', 'asc')
                ->get();
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
