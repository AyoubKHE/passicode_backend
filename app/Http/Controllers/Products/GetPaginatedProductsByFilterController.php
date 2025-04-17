<?php

namespace App\Http\Controllers\Products;

use Exception;
use Throwable;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Products\Product;
use App\Http\Resources\Products\ProductsCollection;
use App\Http\Requests\Products\GetPaginatedProductsByFilterRequest;

class GetPaginatedProductsByFilterController extends Controller
{
    private GetPaginatedProductsByFilterRequest $global_request_object;
    private LengthAwarePaginator $paginated_products;
    private Builder $product_filter_query;
    private array $sent_filter;

    private function prepareUpdatedAt()
    {
        $this->product_filter_query->when(
            array_key_exists('updated_at', $this->sent_filter),
            function (Builder $query) {

                if ($this->sent_filter['updated_at'] === null) {
                    $query->whereNull('updated_at');
                } else {
                    if (array_key_exists("from", $this->sent_filter['updated_at'])) {
                        $min_updated_at = $this->sent_filter['updated_at']['from'];
                    } else {
                        try {
                            $min_updated_at = Product::min("updated_at");
                        } catch (Throwable $th) {
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
                            $max_updated_at = Product::max("updated_at");
                        } catch (Throwable $th) {
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
        $this->product_filter_query->when(
            array_key_exists('created_at', $this->sent_filter),
            function (Builder $query) {
                if (array_key_exists("from", $this->sent_filter['created_at'])) {
                    $min_created_at = $this->sent_filter['created_at']['from'];
                } else {
                    try {
                        $min_created_at = Product::min("created_at");
                    } catch (Throwable $th) {
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
                        $max_created_at = Product::max("created_at");
                    } catch (Throwable $th) {
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

    private function preparePurchasePrice()
    {
        $this->product_filter_query->when(
            array_key_exists('purchase_price', $this->sent_filter),
            function (Builder $query) {
                if (array_key_exists("from", $this->sent_filter['purchase_price'])) {
                    $min_purchase_price = $this->sent_filter['purchase_price']['from'];
                } else {
                    try {
                        $min_purchase_price = Product::min("purchase_price");
                    } catch (Throwable $th) {
                        throw new Exception(
                            'An error occurred while accessing the database. Please try again later.',
                            500
                        );
                    }
                }

                if (array_key_exists("to", $this->sent_filter['purchase_price'])) {
                    $max_purchase_price = $this->sent_filter['purchase_price']['to'];
                } else {
                    try {
                        $max_purchase_price = Product::max("purchase_price");
                    } catch (Throwable $th) {
                        throw new Exception(
                            'An error occurred while accessing the database. Please try again later.',
                            500
                        );
                    }
                }

                $query->whereBetween('purchase_price', [$min_purchase_price, $max_purchase_price]);
            }
        );
    }

    private function prepareExpirationDate()
    {
        $this->product_filter_query->when(
            array_key_exists('expiration_date', $this->sent_filter),
            function (Builder $query) {

                if ($this->sent_filter['expiration_date'] === null) {
                    $query->whereNull('expiration_date');
                } else {
                    if (array_key_exists("from", $this->sent_filter['expiration_date'])) {
                        $min_expiration_date = $this->sent_filter['expiration_date']['from'];
                    } else {
                        try {
                            $min_expiration_date = Product::min("expiration_date");
                        } catch (Throwable $th) {
                            throw new Exception(
                                'An error occurred while accessing the database. Please try again later.',
                                500
                            );
                        }

                    }

                    if (array_key_exists("to", $this->sent_filter['expiration_date'])) {
                        $max_expiration_date = $this->sent_filter['expiration_date']['to'];
                    } else {
                        try {
                            $max_expiration_date = Product::max("expiration_date");
                        } catch (Throwable $th) {
                            throw new Exception(
                                'An error occurred while accessing the database. Please try again later.',
                                500
                            );
                        }

                    }

                    $query->whereBetween(
                        'expiration_date',
                        [$min_expiration_date, $max_expiration_date]
                    );
                }
            }
        );
    }

    private function prepareSold()
    {
        $this->product_filter_query->when(
            array_key_exists('sold', $this->sent_filter),
            function (Builder $query): void {
                $query->where('sold', $this->sent_filter['sold']);
            }
        );
    }

    private function prepareRelatedCategory()
    {
        if (array_key_exists('related_category_id', $this->sent_filter)) {
            $this->product_filter_query->whereHas(
                'categories',
                function (Builder $query) {
                    $query->where(
                        'category_id',
                        $this->sent_filter['related_category_id']
                    );
                }
            );
        }
    }

    private function prepareCode()
    {
        $this->product_filter_query->when(
            array_key_exists('code', $this->sent_filter),
            function (Builder $query) {
                $query->where(
                    'code_start',
                    'like',
                    $this->sent_filter['code'] . "%"
                );
            }
        );
    }

    private function prepareId()
    {
        $this->product_filter_query->when(
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

    private function preparePaginatedProducts()
    {
        $page = (int) $this->global_request_object->get('page', 1);
        $limit = (int) $this->global_request_object->get('limit', 10);

        if ($limit > 100) {
            throw new Exception(
                'Limit must not exceed 100 to ensure optimal performance.',
                400
            );
        }

        $this->sent_filter = $this->global_request_object->validated();

        $this->product_filter_query = Product::query();

        $this->prepareId();

        $this->prepareCode();

        $this->prepareRelatedCategory();

        $this->prepareSold();

        $this->prepareExpirationDate();

        $this->preparePurchasePrice();

        $this->prepareCreatedAt();

        $this->prepareUpdatedAt();

        // $sql = $this->product_filter_query->toRawSql();

        try {
            $this->paginated_products = $this->product_filter_query
                ->paginate(perPage: $limit, page: $page);
        } catch (Throwable $th) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

    }

    public function __invoke(GetPaginatedProductsByFilterRequest $request)
    {
        $this->global_request_object = $request;

        $this->preparePaginatedProducts();

        if (count($this->paginated_products) > 0) {
            return response()->json([
                'products_data' => new ProductsCollection($this->paginated_products),
            ], 200);
        } else {
            return response()->json([
                'products_data' => [
                    'products' => [],
                    'meta' => null
                ],
            ], 200);
        }
    }
}
