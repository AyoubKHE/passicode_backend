<?php

namespace App\Http\Controllers\Categories;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Models\Products\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;


class CategoryCreationPageDataController extends Controller
{
    private Request $global_request_object;
    private array $category_creation_page_data;

    private function prepareCategoryCreationPageData()
    {
        try {
            $this->category_creation_page_data['parent_categories'] =
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

            Log::channel('category_creation_page_data_errors')->error(
                "\n\n" .
                "Description: Failed to get parent categories from database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
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

    public function __invoke(Request $request)
    {
        $this->global_request_object = $request;

        $this->prepareCategoryCreationPageData();

        return response()->json([
            'category_creation_page_data' => $this->category_creation_page_data,
        ], 200);
    }
}
