<?php

namespace App\Http\Controllers\FailedQuantityRequests;

use App\Models\Products\FailedQuantityRequest;
use Exception;
use Throwable;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Http\Resources\Products\FailedQuantityRequestsCollection;
use App\Http\Requests\FailedQuantityRequests\GetPaginatedFailedQuantityRequestsByFilterRequest;

class GetPaginatedFailedQuantityRequestsByFilterController extends Controller
{
    private GetPaginatedFailedQuantityRequestsByFilterRequest $global_request_object;
    private LengthAwarePaginator $paginated_failed_quantity_requests;
    private Builder $failed_quantity_request_filter_query;
    private array $sent_filter;

    private function prepareCreatedAt()
    {
        $this->failed_quantity_request_filter_query->when(
            array_key_exists('created_at', $this->sent_filter),
            function (Builder $query) {
                if (array_key_exists("from", $this->sent_filter['created_at'])) {
                    $min_created_at = $this->sent_filter['created_at']['from'];
                } else {
                    try {
                        $min_created_at = FailedQuantityRequest::min("created_at");
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
                        $max_created_at = FailedQuantityRequest::max("created_at");
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

    private function prepareRequestedQuantity()
    {
        $this->failed_quantity_request_filter_query->when(
            array_key_exists('requested_quantity', $this->sent_filter),
            function (Builder $query) {
                if (array_key_exists("from", $this->sent_filter['requested_quantity'])) {
                    $min_requested_quantity = $this->sent_filter['requested_quantity']['from'];
                } else {
                    try {
                        $min_requested_quantity = FailedQuantityRequest::min("requested_quantity");
                    } catch (Throwable $th) {
                        throw new Exception(
                            'An error occurred while accessing the database. Please try again later.',
                            500
                        );
                    }
                }

                if (array_key_exists("to", $this->sent_filter['requested_quantity'])) {
                    $max_requested_quantity = $this->sent_filter['requested_quantity']['to'];
                } else {
                    try {
                        $max_requested_quantity = FailedQuantityRequest::max("requested_quantity");
                    } catch (Throwable $th) {
                        throw new Exception(
                            'An error occurred while accessing the database. Please try again later.',
                            500
                        );
                    }
                }

                $query->whereBetween('requested_quantity', [$min_requested_quantity, $max_requested_quantity]);
            }
        );
    }

    private function prepareAvailableQuantity()
    {
        $this->failed_quantity_request_filter_query->when(
            array_key_exists('available_quantity', $this->sent_filter),
            function (Builder $query) {
                if (array_key_exists("from", $this->sent_filter['available_quantity'])) {
                    $min_available_quantity = $this->sent_filter['available_quantity']['from'];
                } else {
                    try {
                        $min_available_quantity = FailedQuantityRequest::min("available_quantity");
                    } catch (Throwable $th) {
                        throw new Exception(
                            'An error occurred while accessing the database. Please try again later.',
                            500
                        );
                    }
                }

                if (array_key_exists("to", $this->sent_filter['available_quantity'])) {
                    $max_available_quantity = $this->sent_filter['available_quantity']['to'];
                } else {
                    try {
                        $max_available_quantity = FailedQuantityRequest::max("available_quantity");
                    } catch (Throwable $th) {
                        throw new Exception(
                            'An error occurred while accessing the database. Please try again later.',
                            500
                        );
                    }
                }

                $query->whereBetween('available_quantity', [$min_available_quantity, $max_available_quantity]);
            }
        );
    }

    private function prepareIsCategoryActive()
    {
        $this->failed_quantity_request_filter_query->when(
            array_key_exists('is_category_active', $this->sent_filter),
            function (Builder $query): void {
                $query->where('is_category_active', $this->sent_filter['is_category_active']);
            }
        );
    }

    private function prepareStatus()
    {
        $this->failed_quantity_request_filter_query->when(
            array_key_exists('status', $this->sent_filter),
            function (Builder $query): void {
                $query->where('status', $this->sent_filter['status']);
            }
        );
    }

    private function prepareCategory()
    {
        $this->failed_quantity_request_filter_query->when(
            array_key_exists('category_id', $this->sent_filter),
            function (Builder $query) {
                $query->where(
                    'category_id',
                    $this->sent_filter['category_id']
                );
            }
        );
    }

    private function prepareUser()
    {
        $this->failed_quantity_request_filter_query->when(
            array_key_exists('user_id', $this->sent_filter),
            function (Builder $query) {
                $query->where(
                    'user_id',
                    $this->sent_filter['user_id']
                );
            }
        );
    }

    private function prepareId()
    {
        $this->failed_quantity_request_filter_query->when(
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

    private function preparePaginatedFailedQuantityRequests()
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

        $this->failed_quantity_request_filter_query = FailedQuantityRequest::query();

        $this->prepareId();

        $this->prepareUser();

        $this->prepareCategory();

        $this->prepareStatus();

        $this->prepareIsCategoryActive();

        $this->prepareAvailableQuantity();

        $this->prepareRequestedQuantity();

        $this->prepareCreatedAt();

        // $sql = $this->failed_quantity_request_filter_query->toRawSql();

        try {
            $this->paginated_failed_quantity_requests = $this->failed_quantity_request_filter_query
                ->with('category')
                ->with('user')
                ->orderBy('created_at', 'asc')
                ->paginate(perPage: $limit, page: $page);
        } catch (Throwable $th) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

    }

    public function __invoke(GetPaginatedFailedQuantityRequestsByFilterRequest $request)
    {
        $this->global_request_object = $request;

        $this->preparePaginatedFailedQuantityRequests();

        if (count($this->paginated_failed_quantity_requests) > 0) {
            return response()->json([
                'failed_quantity_requests_data' => new FailedQuantityRequestsCollection($this->paginated_failed_quantity_requests),
            ], 200);
        } else {
            return response()->json([
                'failed_quantity_requests_data' => [
                    'failed_quantity_requests' => [],
                    'meta' => null
                ],
            ], 200);
        }
    }
}
