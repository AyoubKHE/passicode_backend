<?php

namespace App\Http\Controllers\Categories;

use Exception;
use Throwable;
use App\Models\Products\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\UpdateParentCategoryRequest;


class UpdateParentCategoryController extends Controller
{
    private UpdateParentCategoryRequest $global_request_object;
    private Category|null $requested_category;
    private Category|null $new_parent_category;
    private int|null $old_parent_id;
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

                Log::channel('update_parent_category_errors')->error(
                    "\n\n" .
                    "Description: Failed to get old parent category from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Category ID: " . $this->global_request_object->category_id . "\n\n" .
                    "Old Parent Category ID: " . $this->requested_category->parent_id . "\n\n" .
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

            if (!$old_parent_category) {

                Log::channel('update_parent_category_errors')->error(
                    "\n\n" .
                    "Description: Old parent category not found.\n\n" .
                    "Error message: - .\n\n" .
                    "Category ID: " . $this->global_request_object->category_id . "\n\n" .
                    "Old Parent Category ID: " . $this->requested_category->parent_id . "\n\n" .
                    "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );

                throw new Exception(
                    'Old parent category not found.',
                    404
                );
            }

            try {

                $child_categories_count = $old_parent_category->childCategories()->count();
            } catch (Throwable $th) {

                Log::channel('update_parent_category_errors')->error(
                    "\n\n" .
                    "Description: Failed to get old parent categories's child categories count from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Category ID: " . $this->global_request_object->category_id . "\n\n" .
                    "Old Parent Category ID: " . $this->requested_category->parent_id . "\n\n" .
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

            if ($child_categories_count === 1) {
                $old_parent_category->is_leaf_category = true;
                $old_parent_category->updated_at = now();

                try {
                    $is_updated = $old_parent_category->save();

                    if (!$is_updated) {
                        throw new Exception(
                            "- .",
                            500
                        );
                    }
                } catch (Throwable $th) {

                    Log::channel('update_parent_category_errors')->error(
                        "\n\n" .
                        "Description: Failed to update is_leaf_category status of old parent category in database.\n\n" .
                        "Error message: " . $th->getMessage() . "\n\n" .
                        "Category ID: " . $this->global_request_object->category_id . "\n\n" .
                        "Old Parent Category ID: " . $this->requested_category->parent_id . "\n\n" .
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
        }

    }

    private function updateRequestedCategory()
    {
        $this->requested_category->parent_id = $this->new_parent_id;
        $this->requested_category->updated_at = now();

        try {
            $is_updated = $this->requested_category->save();

            if (!$is_updated) {
                throw new Exception(
                    "- .",
                    500
                );
            }
        } catch (Throwable $th) {
            try {
                Log::channel('update_parent_category_errors')->error(
                    "\n\n" .
                    "Description: Failed to update requested category's parent id in database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Category ID: " . $this->global_request_object->category_id . "\n\n" .
                    "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
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

                Log::channel('update_parent_category_errors')->error(
                    "\n\n" .
                    "Description: Failed to get parent category from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Category ID: " . $this->global_request_object->category_id . "\n\n" .
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

                Log::channel('update_parent_category_errors')->error(
                    "\n\n" .
                    "Description: Failed to get new parent category from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Category ID: " . $this->global_request_object->category_id . "\n\n" .
                    "New Parent Category ID: " . $this->new_parent_id . "\n\n" .
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

            if (!$this->new_parent_category) {

                Log::channel('update_parent_category_errors')->error(
                    "\n\n" .
                    "Description: New parent category not found.\n\n" .
                    "Error message: - .\n\n" .
                    "Category ID: " . $this->global_request_object->category_id . "\n\n" .
                    "New Parent Category ID: " . $this->new_parent_id . "\n\n" .
                    "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );

                throw new Exception(
                    'New parent category not found.',
                    404
                );
            }

            try {

                $products_count = $this->new_parent_category->products()->count();
            } catch (Throwable $th) {

                Log::channel('update_parent_category_errors')->error(
                    "\n\n" .
                    "Description: Failed to get new parent categories's products count from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Category ID: " . $this->global_request_object->category_id . "\n\n" .
                    "New Parent Category ID: " . $this->new_parent_id . "\n\n" .
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

            if ($products_count > 0) {

                Log::channel('update_parent_category_errors')->error(
                    "\n\n" .
                    "Description: The category cannot be set as a parent because it already contains products.\n\n" .
                    "Error message: - .\n\n" .
                    "Category ID: " . $this->global_request_object->category_id . "\n\n" .
                    "New Parent Category ID: " . $this->new_parent_id . "\n\n" .
                    "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );

                throw new Exception(
                    "The category cannot be set as a parent because it already contains products.",
                    422
                );
            }

            if ($this->isDescendant($this->new_parent_category)) {

                Log::channel('update_parent_category_errors')->error(
                    "\n\n" .
                    "Description: A category cannot be moved inside one of its own subcategories.\n\n" .
                    "Error message: - .\n\n" .
                    "Category ID: " . $this->global_request_object->category_id . "\n\n" .
                    "New Parent Category ID: " . $this->new_parent_id . "\n\n" .
                    "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );

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

                    if (!$is_updated) {
                        throw new Exception(
                            "- .",
                            500
                        );
                    }
                } catch (Throwable $th) {

                    Log::channel('update_parent_category_errors')->error(
                        "\n\n" .
                        "Description: Failed to update is_leaf_category status of new parent category in database.\n\n" .
                        "Error message: " . $th->getMessage() . "\n\n" .
                        "Category ID: " . $this->global_request_object->category_id . "\n\n" .
                        "New Parent Category ID: " . $this->new_parent_id . "\n\n" .
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
            try {
                Log::channel('update_parent_category_errors')->error(
                    "\n\n" .
                    "Description: Failed to get requested category from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Category ID: " . $this->global_request_object->category_id . "\n\n" .
                    "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
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

        if (!$this->requested_category) {
            try {
                Log::channel('update_parent_category_errors')->error(
                    "\n\n" .
                    "Description: Requested category not found.\n\n" .
                    "Error message: - .\n\n" .
                    "Category ID: " . $this->global_request_object->category_id . "\n\n" .
                    "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
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

        $this->new_parent_id = $this->global_request_object->validated()['new_parent_id'];

        if ($this->requested_category->id === $this->new_parent_id) {
            try {
                Log::channel('update_parent_category_errors')->error(
                    "\n\n" .
                    "Description: The parent ID cannot be the same as the category ID. Please choose a different parent category.\n\n" .
                    "Error message: - .\n\n" .
                    "Category ID: " . $this->global_request_object->category_id . "\n\n" .
                    "New Parent Category ID: " . $this->new_parent_id . "\n\n" .
                    "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
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
                'The parent ID cannot be the same as the category ID. Please choose a different parent category.',
                422
            );
        }

        if ($this->requested_category->parent_id === $this->new_parent_id) {
            try {
                Log::channel('update_parent_category_errors')->error(
                    "\n\n" .
                    "Description: No updates were made. Please ensure there is at least one modification before submitting.\n\n" .
                    "Error message: - .\n\n" .
                    "Category ID: " . $this->global_request_object->category_id . "\n\n" .
                    "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
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
                'No updates were made. Please ensure there is at least one modification before submitting.',
                400
            );
        }

        $this->old_parent_id = $this->requested_category->parent_id;
    }

    private function logRequest()
    {
        try {
            Log::channel('update_parent_category_requests')->info(
                "\n\n" .
                "Description: The parent category has been successfully updated.\n\n" .
                "Category ID: " . $this->global_request_object->category_id . "\n\n" .
                "Old Parent Category ID: << " . $this->old_parent_id . " >>\n\n" .
                "New Parent Category ID: << " . $this->new_parent_id . " >>\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );
        } catch (Throwable $th) {
            //throw $th;
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

        $this->logRequest();

        return response()->json([
            'message' => "The parent category has been successfully updated.",
            'new_parent_category' => $this->new_parent_id ? [
                'id' => $this->new_parent_category->id,
                'name' => $this->new_parent_category->name,
            ] : null
        ], 200);
    }
}
