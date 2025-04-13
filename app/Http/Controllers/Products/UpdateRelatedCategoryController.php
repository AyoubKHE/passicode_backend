<?php

namespace App\Http\Controllers\Products;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Models\Products\Product;
use App\Models\Products\Category;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Products\Product_Category;


class UpdateRelatedCategoryController extends Controller
{
    private Request $global_request_object;
    private Product|null $requested_product;
    private Category|null $requested_category;


    private function updateCategory()
    {
        $this->requested_category["updated_at"] = now();

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


    private function updateProduct()
    {
        $this->requested_product["updated_at"] = now();

        try {
            $is_updated = $this->requested_product->save();
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


    private function updateRelatedCategory()
    {

        if ($this->global_request_object->type === "add") {
            try {
                $is_inserted = Product_Category::insert([
                    'product_id' => $this->requested_product->id,
                    'category_id' => $this->global_request_object->category_id
                ]);
            } catch (Throwable $th) {
                throw new Exception(
                    'An error occurred while accessing the database. Please try again later.',
                    500
                );
            }

            if (!$is_inserted) {
                throw new Exception(
                    'An error occurred while accessing the database. Please try again later.',
                    500
                );
            }
        } else {
            try {
                $is_deleted = Product_Category::where(
                    'product_id',
                    $this->requested_product->id,
                )
                    ->where(
                        'category_id',
                        $this->global_request_object->category_id
                    )
                    ->delete();
            } catch (Throwable $th) {
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

    }


    private function checkModificationsAreMade()
    {
        if ($this->global_request_object->type === "add") {
            foreach ($this->requested_product->categories as $related_category) {
                if (
                    $related_category->id === (int) $this->global_request_object->category_id
                ) {
                    throw new Exception(
                        "Product already belongs to this category.",
                        422
                    );
                }
            }
        } else {

            $exist = false;

            foreach ($this->requested_product->categories as $related_category) {
                if (
                    $related_category->id === (int) $this->global_request_object->category_id
                ) {
                    $exist = true;
                    break;
                }
            }

            if (!$exist) {
                throw new Exception(
                    "The product isn't related with this category to be removed.",
                    422
                );
            }
        }
    }


    private function checkRelatedCategoriesCount()
    {
        if (
            count($this->requested_product->categories) === 1 &&
            $this->global_request_object->type === "delete"
        ) {
            throw new Exception(
                'Products must be related at least to one category.',
                422
            );
        }
    }


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

        if (!$this->requested_category) {
            throw new Exception('Requested category not found.', 404);
        }
    }


    private function loadRequestedProduct()
    {
        try {

            $this->requested_product = Product::where(
                "id",
                $this->global_request_object->product_id
            )
                ->with(['categories'])
                ->first();
        } catch (Throwable $th) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$this->requested_product) {
            throw new Exception('Requested product not found.', 404);
        }
    }


    private function validateType()
    {
        if (
            $this->global_request_object->type !== "add" &&
            $this->global_request_object->type !== "delete"
        ) {
            throw new Exception(
                "The type should be either 'add' or 'delete'.",
                422
            );
        }
    }


    public function __invoke(Request $request)
    {
        $this->global_request_object = $request;

        $this->validateType();

        $this->loadRequestedProduct();

        $this->loadRequestedCategory();

        $this->checkRelatedCategoriesCount();

        $this->checkModificationsAreMade();

        DB::transaction(function () {

            $this->updateRelatedCategory();

            $this->updateProduct();

            $this->updateCategory();
        });



        return response()->json([
            'message' => "Related category updated successfully.",
        ], 200);
    }
}
