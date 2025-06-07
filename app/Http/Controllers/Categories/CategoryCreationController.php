<?php

namespace App\Http\Controllers\Categories;

use Exception;
use Throwable;
use App\Models\Products\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Products\CategoryResource;
use App\Http\Requests\Categories\CategoryCreationRequest;


class CategoryCreationController extends Controller
{
    private CategoryCreationRequest $global_request_object;
    private int $category_id;
    private array $prepared_category;
    private Category|null $stored_category;


    private function eagerLoadRelations(): void
    {
        try {
            $this->stored_category->load('parentCategory');
        } catch (Throwable $th) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }
    }


    private function manageParentCategory()
    {
        if ($this->stored_category->parent_id) {
            try {

                $parent_category = Category::where(
                    "id",
                    $this->stored_category->parent_id
                )
                    ->first();
            } catch (Throwable $th) {
                throw new Exception(
                    'An error occurred while accessing the database. Please try again later.',
                    500
                );
            }

            if ($parent_category->is_leaf_category) {
                $parent_category->is_leaf_category = false;
                $parent_category->updated_at = now();

                try {
                    $is_updated = $parent_category->save();
                } catch (Throwable $throwable) {
                    throw new Exception(
                        'An error occurred while accessing the database. Please try again later.',
                        500
                    );
                }

                if (!$is_updated) {
                    throw new Exception(
                        'An error occurred while accessing the database. Please try again later.',
                        500
                    );
                }
            }
        }


    }


    private function storeCategory(): void
    {
        try {
            $this->stored_category = Category::create($this->prepared_category);
        } catch (Throwable $throwable) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$this->stored_category) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }
    }


    private function storeCategoryImage(): void
    {
        try {
            $table_status = DB::select("SHOW TABLE STATUS LIKE 'categories'");

        } catch (Throwable $th) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (count($table_status) === 0) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        $this->category_id = $table_status[0]->Auto_increment;

        $image_file = $this->global_request_object->file('image');
        $folder_path = 'categories/id_' . $this->category_id;

        $this->prepared_category["image_path"] = $image_file->store($folder_path);
        if (!$this->prepared_category["image_path"]) {
            throw new Exception("An error occurred while saving the category's image.", 500);
        }

        unset($this->prepared_category["image"]);
    }


    private function preparingData(): void
    {
        $this->prepared_category = $this->global_request_object->validated();

        $this->prepared_category['quantity'] = 0;

        $this->prepared_category['is_leaf_category'] = true;

        $this->prepared_category['created_at'] = now();

        $this->prepared_category['updated_at'] = null;
    }


    public function __invoke(CategoryCreationRequest $request): JsonResponse
    {
        $this->global_request_object = $request;

        $this->preparingData();

        $this->storeCategoryImage();

        try {

            DB::transaction(function () {

                $this->storeCategory();

                $this->manageParentCategory();

                $this->eagerLoadRelations();
            });

            return response()->json([
                'message' => 'Category created successfully.',
                'category' => new CategoryResource($this->stored_category),
            ], 201);

        } catch (Throwable $throwable) {

            Storage::deleteDirectory("categories/id_" . $this->category_id);

            throw $throwable;
        }
    }
}
