<?php

namespace App\Http\Controllers\Categories;

use Exception;
use Throwable;
use App\Models\Products\Category;
use App\Http\Resources\Products\CategoryResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GetCategoryByIdController extends Controller
{
    private Request $global_request_object;
    private Category|null $requested_category;

    private function loadRequestedCategory()
    {
        try {

            $this->requested_category = Category::where(
                "id",
                $this->global_request_object->category_id
            )
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

        $this->loadRequestedCategory();

        if ($this->requested_category) {
            return response()->json([
                'category' => new CategoryResource($this->requested_category),
            ], 200);
        } else {
            return response()->json([
                'category' => null,
            ], 200);
        }
    }
}
