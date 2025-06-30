<?php

namespace App\Http\Controllers\FailedQuantityRequests;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Products\FailedQuantityRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Http\Resources\Products\FailedQuantityRequestsCollection;


class GetPaginatedFailedQuantityRequestsController extends Controller
{
    private Request $global_request_object;
    private LengthAwarePaginator $paginated_failed_quantity_requests;

    private function preparePaginatedFailedQuantityRequests()
    {
        $page = (int) $this->global_request_object->get('page', 1);
        $limit = (int) $this->global_request_object->get('limit', 10);
        $id = $this->global_request_object->get('id', "");

        if ($limit > 100) {
            throw new Exception(
                'Limit must not exceed 100 to ensure optimal performance.',
                400
            );
        }
        if ($id) {
            try {
                $this->paginated_failed_quantity_requests = FailedQuantityRequest::where(
                    'id',
                    'like',
                    $id . "%"
                )->where(
                        'status',
                        'not_settled'
                    )
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
        } else {
            try {
                $this->paginated_failed_quantity_requests = FailedQuantityRequest::where(
                    'status',
                    'not_settled'
                )
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
    }

    public function __invoke(Request $request)
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
