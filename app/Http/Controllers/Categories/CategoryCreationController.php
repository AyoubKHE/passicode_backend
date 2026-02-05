<?php

namespace App\Http\Controllers\Categories;

use Exception;
use Throwable;
use App\Models\Products\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Products\CategoryResource;
use App\Http\Requests\Categories\CategoryCreationRequest;
use Illuminate\Http\UploadedFile;


class CategoryCreationController extends Controller
{
    private CategoryCreationRequest $global_request_object;
    private array $prepared_category;
    private Category|null $stored_category;
    private UploadedFile $category_image;


    private function eagerLoadRelations(): void
    {
        try {
            $this->stored_category->load('parentCategory');
        } catch (Throwable $th) {
            try {
                Log::channel('category_creation_errors')->error(
                    "\n\n" .
                    "Description: Failed to load Category model relations << ->load() function >>.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
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


    private function storeCategoryImage(): void
    {
        $image_path = "";

        try {
            $image_path = $this->category_image->store(
                'categories/id_' . $this->stored_category->id
            );

            if (!$image_path) {
                throw new Exception(
                    "- .",
                    500
                );
            }
        } catch (Throwable $th) {
            try {
                Log::channel('category_creation_errors')->error(
                    "\n\n" .
                    "Description: An error occurred while saving the category's image.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
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
                "An error occurred while saving the category's image.",
                500
            );
        }

        try {

            $is_updated = $this->stored_category
                ->update(["image_path" => $image_path]);

            if (!$is_updated) {
                throw new Exception(
                    "- .",
                    500
                );
            }

        } catch (Throwable $th) {
            try {
                Log::channel('category_creation_errors')->error(
                    "\n\n" .
                    "Description: Failed to update category's image path in database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Category ID: " . $this->stored_category->id . "\n\n" .
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
                "Failed to update category's image path in database.",
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

                Log::channel('category_creation_errors')->error(
                    "\n\n" .
                    "Description: Failed to get parent category from database.\n\n" .
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

            if ($parent_category->is_leaf_category) {
                $parent_category->is_leaf_category = false;
                $parent_category->updated_at = now();

                try {
                    $is_updated = $parent_category->save();

                    if (!$is_updated) {
                        throw new Exception(
                            "- .",
                            500
                        );
                    }

                } catch (Throwable $th) {

                    Log::channel('category_creation_errors')->error(
                        "\n\n" .
                        "Description: Failed to update parent category is_leaf_category status.\n\n" .
                        "Error message: - .\n\n" .
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


    private function storeCategory(): void
    {
        try {
            $this->stored_category = Category::create($this->prepared_category);

            if (!$this->stored_category) {
                throw new Exception(
                    "- .",
                    500
                );
            }

        } catch (Throwable $th) {
            try {
                Log::channel('category_creation_errors')->error(
                    "\n\n" .
                    "Description: Failed to store new category in database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
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


    private function preparingData(): void
    {
        $this->prepared_category = $this->global_request_object->validated();

        $this->prepared_category['quantity'] = 0;

        $this->prepared_category["image_path"] = "temp_path";

        $this->prepared_category['is_leaf_category'] = true;

        $this->prepared_category['created_at'] = now();

        $this->prepared_category['updated_at'] = null;

        $this->category_image = $this->global_request_object->file('image');

        unset($this->prepared_category["image"]);
    }


    private function logRequest()
    {
        try {

            Log::channel('category_creation_requests')->info(
                "\n\n" .
                "Description: Category created successfully.\n\n" .
                "Category Data: \n" .
                json_encode($this->stored_category->toArray(), JSON_PRETTY_PRINT) . "\n\n" .
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

    public function __invoke(CategoryCreationRequest $request): JsonResponse
    {
        $this->global_request_object = $request;

        $this->preparingData();

        try {

            DB::transaction(function () {

                $this->storeCategory();

                $this->manageParentCategory();

                $this->eagerLoadRelations();

                $this->storeCategoryImage();
            });

            $this->logRequest();

            return response()->json([
                'message' => 'Category created successfully.',
                'category' => new CategoryResource($this->stored_category),
            ], 201);

        } catch (Throwable $th) {

            if ($th->getMessage() === "Failed to update category's image path in database.") {
                Storage::deleteDirectory("categories/id_" . $this->stored_category->id);

                throw new Exception(
                    'An error occurred while accessing the database. Please try again later.',
                    500
                );
            } else {
                throw $th;
            }
        }
    }
}
