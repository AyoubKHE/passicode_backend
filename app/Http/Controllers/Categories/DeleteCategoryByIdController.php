<?php

namespace App\Http\Controllers\Categories;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\Products\Category;

class DeleteCategoryByIdController extends Controller
{
    private Request $global_request_object;
    private Category|null $requested_category;

    private function deleteRequestedCategoryImageOnDisk()
    {
        if (!Storage::deleteDirectory("categories/id_" . $this->requested_category->id)) {
            throw new Exception(
                "An error occurred while deleting the category's image.",
                500
            );
        }
    }

    private function deleteRequestedCategory()
    {
        try {
            $is_deleted = $this->requested_category->delete();
        } catch (Throwable $throwable) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$is_deleted) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }
    }

    private function loadRequestedCategory()
    {
        try {

            $this->requested_category = Category::where(
                "id",
                $this->global_request_object->category_id
            )->first();
        } catch (Throwable $th) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$this->requested_category) {
            throw new Exception('Requested category not found.', 404);
        }
    }

    public function __invoke(Request $request)
    {
        $this->global_request_object = $request;

        $this->loadRequestedCategory();

        DB::transaction(function () {

            $this->deleteRequestedCategory();

            $this->deleteRequestedCategoryImageOnDisk();
        });

        return response()->json([
            'message' => 'Category deleted successfully.',
        ], 200);

    }
}
