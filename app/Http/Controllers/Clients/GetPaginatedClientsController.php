<?php

namespace App\Http\Controllers\Clients;

use Exception;
use Throwable;
use App\Models\Users\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Clients\ClientsCollectionV2;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;


class GetPaginatedClientsController extends Controller
{
    private Request $global_request_object;
    private LengthAwarePaginator $paginated_clients;

    private function preparePaginatedClients()
    {
        $page = (int) $this->global_request_object->get('page', 1);
        $limit = (int) $this->global_request_object->get('limit', 10);
        $full_name = $this->global_request_object->get('full_name', "");

        if ($limit > 100) {
            throw new Exception(
                'Limit must not exceed 100 to ensure optimal performance.',
                400
            );
        }
        if ($full_name) {
            try {
                $this->paginated_clients = User::whereRaw(
                    "CONCAT(first_name, ' ', last_name) LIKE ?",
                    ["%{$full_name}%"]
                )
                    ->where(
                        'role',
                        'Client',
                    )
                    ->with('client')
                    ->paginate(perPage: $limit, page: $page);

            } catch (Throwable $th) {
                throw new Exception(
                    'An error occurred while accessing the database. Please try again later.',
                    500
                );
            }
        } else {
            try {
                $this->paginated_clients = User::where(
                    'role',
                    'Client',
                )
                    ->with('client')
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

        $this->preparePaginatedClients();

        if (count($this->paginated_clients) > 0) {
            return response()->json([
                'clients_data' => new ClientsCollectionV2($this->paginated_clients),
            ], 200);
        } else {
            return response()->json([
                'clients_data' => [
                    'clients' => [],
                    'meta' => null
                ],
            ], 200);
        }
    }
}
