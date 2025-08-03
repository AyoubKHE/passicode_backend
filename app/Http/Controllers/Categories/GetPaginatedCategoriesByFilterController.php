<?php

namespace App\Http\Controllers\Categories;

use Exception;
use Throwable;
use App\Models\Products\Category;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\Products\CategoriesCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Http\Requests\Categories\GetPaginatedCategoriesByFilterRequest;

class GetPaginatedCategoriesByFilterController extends Controller
{
    private GetPaginatedCategoriesByFilterRequest $global_request_object;
    private LengthAwarePaginator $paginated_categories;
    private Builder $category_filter_query;
    private array $sent_filter;

    private function prepareUpdatedAt()
    {
        $this->category_filter_query->when(
            array_key_exists('updated_at', $this->sent_filter),
            function (Builder $query) {

                if ($this->sent_filter['updated_at'] === null) {
                    $query->whereNull('updated_at');
                } else {
                    if (array_key_exists("from", $this->sent_filter['updated_at'])) {
                        $min_updated_at = $this->sent_filter['updated_at']['from'];
                    } else {
                        try {
                            $min_updated_at = Category::min("updated_at");
                        } catch (Throwable $th) {

                            Log::channel('get_paginated_categories_by_filter_errors')->error(
                                "\n\n" .
                                "Description: Failed to get minimum updated_at of categories from database.\n\n" .
                                "Error message: " . $th->getMessage() . "\n\n" .
                                "Page: " . (int) $this->global_request_object->get('page', 1) . "\n\n" .
                                "Limit: " . (int) $this->global_request_object->get('limit', 10) . "\n\n" .
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

                    if (array_key_exists("to", $this->sent_filter['updated_at'])) {
                        $max_updated_at = $this->sent_filter['updated_at']['to'];
                    } else {
                        try {
                            $max_updated_at = Category::max("updated_at");
                        } catch (Throwable $th) {

                            Log::channel('get_paginated_categories_by_filter_errors')->error(
                                "\n\n" .
                                "Description: Failed to get maximum updated_at of categories from database.\n\n" .
                                "Error message: " . $th->getMessage() . "\n\n" .
                                "Page: " . (int) $this->global_request_object->get('page', 1) . "\n\n" .
                                "Limit: " . (int) $this->global_request_object->get('limit', 10) . "\n\n" .
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

                    $query->whereBetween('updated_at', [$min_updated_at, $max_updated_at]);
                }
            }
        );
    }

    private function prepareCreatedAt()
    {
        $this->category_filter_query->when(
            array_key_exists('created_at', $this->sent_filter),
            function (Builder $query) {
                if (array_key_exists("from", $this->sent_filter['created_at'])) {
                    $min_created_at = $this->sent_filter['created_at']['from'];
                } else {
                    try {
                        $min_created_at = Category::min("created_at");
                    } catch (Throwable $th) {

                        Log::channel('get_paginated_categories_by_filter_errors')->error(
                            "\n\n" .
                            "Description: Failed to get minimum created_at of categories from database.\n\n" .
                            "Error message: " . $th->getMessage() . "\n\n" .
                            "Page: " . (int) $this->global_request_object->get('page', 1) . "\n\n" .
                            "Limit: " . (int) $this->global_request_object->get('limit', 10) . "\n\n" .
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

                if (array_key_exists("to", $this->sent_filter['created_at'])) {
                    $max_created_at = $this->sent_filter['created_at']['to'];
                } else {
                    try {
                        $max_created_at = Category::max("created_at");
                    } catch (Throwable $th) {

                        Log::channel('get_paginated_categories_by_filter_errors')->error(
                            "\n\n" .
                            "Description: Failed to get maximum created_at of categories from database.\n\n" .
                            "Error message: " . $th->getMessage() . "\n\n" .
                            "Page: " . (int) $this->global_request_object->get('page', 1) . "\n\n" .
                            "Limit: " . (int) $this->global_request_object->get('limit', 10) . "\n\n" .
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

                $query->whereBetween('created_at', [$min_created_at, $max_created_at]);
            }
        );
    }

    private function prepareParentCategory()
    {
        $this->category_filter_query->when(
            array_key_exists('parent_category_id', $this->sent_filter),
            function (Builder $query) {

                if ($this->sent_filter['parent_category_id'] === null) {
                    $query->whereNull('parent_id');
                } else {
                    $query->where(
                        'parent_id',
                        $this->sent_filter['parent_category_id']
                    );
                }
            }
        );
    }

    private function prepareIsLeafCategory()
    {
        $this->category_filter_query->when(
            array_key_exists('is_leaf_category', $this->sent_filter),
            function (Builder $query): void {
                $query->where('is_leaf_category', $this->sent_filter['is_leaf_category']);
            }
        );
    }

    private function prepareIsActive()
    {
        $this->category_filter_query->when(
            array_key_exists('is_active', $this->sent_filter),
            function (Builder $query): void {
                $query->where('is_active', $this->sent_filter['is_active']);
            }
        );
    }

    private function prepareQuantity()
    {
        $this->category_filter_query->when(
            array_key_exists('quantity', $this->sent_filter),
            function (Builder $query) {
                if (array_key_exists("from", $this->sent_filter['quantity'])) {
                    $min_quantity = $this->sent_filter['quantity']['from'];
                } else {
                    try {
                        $min_quantity = Category::min("quantity");
                    } catch (Throwable $th) {

                        Log::channel('get_paginated_categories_by_filter_errors')->error(
                            "\n\n" .
                            "Description: Failed to get minimum quantity of categories from database.\n\n" .
                            "Error message: " . $th->getMessage() . "\n\n" .
                            "Page: " . (int) $this->global_request_object->get('page', 1) . "\n\n" .
                            "Limit: " . (int) $this->global_request_object->get('limit', 10) . "\n\n" .
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

                if (array_key_exists("to", $this->sent_filter['quantity'])) {
                    $max_quantity = $this->sent_filter['quantity']['to'];
                } else {
                    try {
                        $max_quantity = Category::max("quantity");
                    } catch (Throwable $th) {

                        Log::channel('get_paginated_categories_by_filter_errors')->error(
                            "\n\n" .
                            "Description: Failed to get maximum quantity of categories from database.\n\n" .
                            "Error message: " . $th->getMessage() . "\n\n" .
                            "Page: " . (int) $this->global_request_object->get('page', 1) . "\n\n" .
                            "Limit: " . (int) $this->global_request_object->get('limit', 10) . "\n\n" .
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

                $query->whereBetween('quantity', [$min_quantity, $max_quantity]);
            }
        );
    }

    private function prepareDiscount()
    {
        $this->category_filter_query->when(
            array_key_exists('discount', $this->sent_filter),
            function (Builder $query) {
                if (array_key_exists("from", $this->sent_filter['discount'])) {
                    $min_discount = $this->sent_filter['discount']['from'];
                } else {
                    try {
                        $min_discount = Category::min("discount");
                        if (!$min_discount) {
                            $min_discount = 0;
                        }
                    } catch (Throwable $th) {

                        Log::channel('get_paginated_categories_by_filter_errors')->error(
                            "\n\n" .
                            "Description: Failed to get minimum discount of categories from database.\n\n" .
                            "Error message: " . $th->getMessage() . "\n\n" .
                            "Page: " . (int) $this->global_request_object->get('page', 1) . "\n\n" .
                            "Limit: " . (int) $this->global_request_object->get('limit', 10) . "\n\n" .
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

                if (array_key_exists("to", $this->sent_filter['discount'])) {
                    $max_discount = $this->sent_filter['discount']['to'];
                } else {
                    try {
                        $max_discount = Category::max("discount");
                        if (!$max_discount) {
                            $max_discount = 0;
                        }
                    } catch (Throwable $th) {

                        Log::channel('get_paginated_categories_by_filter_errors')->error(
                            "\n\n" .
                            "Description: Failed to get maximum discount of categories from database.\n\n" .
                            "Error message: " . $th->getMessage() . "\n\n" .
                            "Page: " . (int) $this->global_request_object->get('page', 1) . "\n\n" .
                            "Limit: " . (int) $this->global_request_object->get('limit', 10) . "\n\n" .
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

                $query->whereBetween('discount', [$min_discount, $max_discount]);
            }
        );
    }

    private function preparePrice()
    {
        $this->category_filter_query->when(
            array_key_exists('price', $this->sent_filter),
            function (Builder $query) {
                if (array_key_exists("from", $this->sent_filter['price'])) {
                    $min_price = $this->sent_filter['price']['from'];
                } else {
                    try {
                        $min_price = Category::min("price");
                        if (!$min_price) {
                            $min_price = 0;
                        }
                    } catch (Throwable $th) {

                        Log::channel('get_paginated_categories_by_filter_errors')->error(
                            "\n\n" .
                            "Description: Failed to get minimum price of categories from database.\n\n" .
                            "Error message: " . $th->getMessage() . "\n\n" .
                            "Page: " . (int) $this->global_request_object->get('page', 1) . "\n\n" .
                            "Limit: " . (int) $this->global_request_object->get('limit', 10) . "\n\n" .
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

                if (array_key_exists("to", $this->sent_filter['price'])) {
                    $max_price = $this->sent_filter['price']['to'];
                } else {
                    try {
                        $max_price = Category::max("price");
                        if (!$max_price) {
                            $max_price = 0;
                        }
                    } catch (Throwable $th) {

                        Log::channel('get_paginated_categories_by_filter_errors')->error(
                            "\n\n" .
                            "Description: Failed to get maximum price of categories from database.\n\n" .
                            "Error message: " . $th->getMessage() . "\n\n" .
                            "Page: " . (int) $this->global_request_object->get('page', 1) . "\n\n" .
                            "Limit: " . (int) $this->global_request_object->get('limit', 10) . "\n\n" .
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

                $query->whereBetween('price', [$min_price, $max_price]);
            }
        );
    }

    private function prepareName()
    {
        $this->category_filter_query->when(
            array_key_exists('name', $this->sent_filter),
            function (Builder $query) {
                $query->where(
                    'name',
                    'like',
                    "%" . $this->sent_filter['name'] . "%"
                );
            }
        );
    }

    private function prepareId()
    {
        $this->category_filter_query->when(
            array_key_exists('id', $this->sent_filter),
            function (Builder $query) {
                $query->where(
                    'id',
                    'like',
                    "%" . $this->sent_filter['id'] . "%"
                );
            }
        );
    }

    private function preparePaginatedCategories()
    {
        $page = (int) $this->global_request_object->get('page', 1);
        $limit = (int) $this->global_request_object->get('limit', 10);

        if ($limit > 100) {

            Log::channel('get_paginated_categories_by_filter_errors')->error(
                "\n\n" .
                "Description: Limit must not exceed 100 to ensure optimal performance.\n\n" .
                "Error message: - .\n\n" .
                "Page: " . $page . "\n\n" .
                "Limit: " . $limit . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'Limit must not exceed 100 to ensure optimal performance.',
                400
            );
        }

        $this->sent_filter = $this->global_request_object->validated();

        $this->category_filter_query = Category::query();

        $this->prepareId();

        $this->prepareName();

        $this->preparePrice();

        $this->prepareDiscount();

        $this->prepareQuantity();

        $this->prepareIsActive();

        $this->prepareIsLeafCategory();

        $this->prepareParentCategory();

        $this->prepareCreatedAt();

        $this->prepareUpdatedAt();

        // $sql = $this->category_filter_query->toRawSql();

        try {
            $this->paginated_categories = $this->category_filter_query
                ->paginate(perPage: $limit, page: $page);
        } catch (Throwable $th) {

            Log::channel('get_paginated_categories_by_filter_errors')->error(
                "\n\n" .
                "Description: Failed to get paginated categories by filter from database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Page: " . $page . "\n\n" .
                "Limit: " . $limit . "\n\n" .
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

    public function __invoke(GetPaginatedCategoriesByFilterRequest $request)
    {
        $this->global_request_object = $request;

        $this->preparePaginatedCategories();

        if (count($this->paginated_categories) > 0) {
            return response()->json([
                'categories_data' => new CategoriesCollection($this->paginated_categories),
            ], 200);
        } else {
            return response()->json([
                'categories_data' => [
                    'categories' => [],
                    'meta' => null
                ],
            ], 200);
        }
    }
}
