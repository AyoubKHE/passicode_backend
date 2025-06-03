<?php

namespace App\Http\Controllers\Categories;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Models\Products\Category;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\Products\CategoriesCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;


class getParentCategories extends Controller
{
    private Request $global_request_object;
    private array $parent_categories;

    private function prepareParentCategories()
    {
        try {
            $this->parent_categories =
                Category::select('id', 'name')
                    ->whereNotExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('products')
                            ->whereColumn('products.category_id', 'categories.id');
                    })
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

        $this->prepareParentCategories();

        return response()->json([
            'parent_categories' => $this->parent_categories,
        ], 200);
    }
}
