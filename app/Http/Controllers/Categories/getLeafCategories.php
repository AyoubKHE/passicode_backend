<?php

namespace App\Http\Controllers\Categories;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Models\Products\Category;
use App\Http\Controllers\Controller;


class getLeafCategories extends Controller
{
    private Request $global_request_object;
    private array $leaf_categories;

    private function prepareLeafCategories()
    {
        try {
            $this->leaf_categories =
                Category::where('is_leaf_category', 1)
                    ->orderBy('id', 'asc')
                    ->get()
                    ->toArray();
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

        $this->prepareLeafCategories();

        return response()->json([
            'leaf_categories' => $this->leaf_categories,
        ], 200);
    }
}
