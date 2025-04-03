<?php

namespace App\Http\Controllers\Categories;

use Exception;
use Throwable;
use App\Models\Products\Category;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\UpdateParentCategoryRequest;


class UpdateParentCategoryController extends Controller
{
    private UpdateParentCategoryRequest $global_request_object;
    private Category|null $requested_category;
    private Category|null $new_parent_category;
    private int|null $new_parent_id;


    private function updateOldParentCategory()
    {
        if ($this->requested_category->parent_id) {
            try {

                $old_parent_category = Category::where(
                    "id",
                    $this->requested_category->parent_id
                )->first();
            } catch (Throwable $th) {
                throw new Exception(
                    'An error occurred while accessing the database. Please try again later.',
                    500
                );
            }

            if (!$old_parent_category) {
                throw new Exception('Old parent category not found.', 404);
            }

            try {

                $child_categories_count = $old_parent_category->childCategories()->count();
            } catch (Throwable $th) {
                throw new Exception(
                    'An error occurred while accessing the database. Please try again later.',
                    500
                );
            }

            if ($child_categories_count === 1) {
                $old_parent_category->is_leaf_category = true;
                $old_parent_category->updated_at = now();

                try {
                    $is_updated = $old_parent_category->save();
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

    private function updateRequestedCategory()
    {
        $this->requested_category->parent_id = $this->new_parent_id;
        $this->requested_category->updated_at = now();

        try {
            $is_updated = $this->requested_category->save();
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

    private function isDescendant($parent)
    {
        while ($parent) {
            if ($parent->parent_id === $this->requested_category->id) {
                return true;
            }

            try {

                $parent = $parent->parentCategory;
            } catch (Throwable $th) {
                throw new Exception(
                    'An error occurred while accessing the database. Please try again later.',
                    500
                );
            }
        }
        return false;
    }
    private function updateNewParentCategory()
    {
        if ($this->new_parent_id) {
            try {

                $this->new_parent_category = Category::where(
                    "id",
                    $this->new_parent_id
                )->first();
            } catch (Throwable $th) {
                throw new Exception(
                    'An error occurred while accessing the database. Please try again later.',
                    500
                );
            }

            if (!$this->new_parent_category) {
                throw new Exception('New parent category not found.', 404);
            }

            try {

                $products_count = $this->new_parent_category->products()->count();
            } catch (Throwable $th) {
                throw new Exception(
                    'An error occurred while accessing the database. Please try again later.',
                    500
                );
            }

            if ($products_count > 0) {
                throw new Exception(
                    "The category cannot be set as a parent because it already contains products.",
                    422
                );
            }

            if ($this->isDescendant($this->new_parent_category)) {
                throw new Exception(
                    'A category cannot be moved inside one of its own subcategories.',
                    422
                );
            }

            if ($this->new_parent_category->is_leaf_category) {

                $this->new_parent_category->is_leaf_category = false;
                $this->new_parent_category->updated_at = now();

                try {
                    $is_updated = $this->new_parent_category->save();
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

        $this->new_parent_id = $this->global_request_object->validated()['new_parent_id'];

        if ($this->requested_category->id === $this->new_parent_id) {
            throw new Exception(
                'The parent ID cannot be the same as the category ID. Please choose a different parent category.',
                422
            );
        }

        if ($this->requested_category->parent_id === $this->new_parent_id) {
            throw new Exception(
                'No updates were made. Please ensure there is at least one modification before submitting.',
                400
            );
        }
    }


    public function __invoke(UpdateParentCategoryRequest $request)
    {
        $this->global_request_object = $request;

        $this->loadRequestedCategory();

        DB::transaction(function () {

            $this->updateOldParentCategory();

            $this->updateRequestedCategory();

            $this->updateNewParentCategory();
        });

        return response()->json([
            'message' => "The parent category has been successfully updated.",
            'new_parent_category' => $this->new_parent_id ? [
                'id' => $this->new_parent_category->id,
                'name' => $this->new_parent_category->name,
            ] : null
        ], 200);
    }
}
