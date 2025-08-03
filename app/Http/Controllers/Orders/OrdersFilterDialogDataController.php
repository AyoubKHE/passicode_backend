<?php

namespace App\Http\Controllers\Orders;

use Exception;
use Throwable;
use App\Models\Users\User;
use Illuminate\Http\Request;
use App\Models\Products\Category;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;


class OrdersFilterDialogDataController extends Controller
{
    private Request $global_request_object;
    private array $orders_filter_dialog_data;

    private function prepareLeafCategories()
    {
        try {
            $this->orders_filter_dialog_data['leaf_categories'] =
                Category::select('id', 'name')
                    ->where('is_leaf_category', 1)
                    ->orderBy('id', 'asc')
                    ->get()
                    ->toArray();
        } catch (Throwable $th) {

            Log::channel('orders_filter_dialog_data_errors')->error(
                "\n\n" .
                "Description: Failed to get leaf categories from database.\n\n" .
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
    }

    private function prepareClients()
    {
        try {
            $this->orders_filter_dialog_data['clients'] =
                User::select('id', 'first_name', 'last_name')
                    ->where('role', 'Client')
                    ->orderBy('id', 'asc')
                    ->get()
                    ->toArray();
        } catch (Throwable $th) {

            Log::channel('orders_filter_dialog_data_errors')->error(
                "\n\n" .
                "Description: Failed to get clients from database.\n\n" .
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
    }

    public function __invoke(Request $request)
    {
        $this->global_request_object = $request;

        $this->prepareClients();

        $this->prepareLeafCategories();

        return response()->json([
            'orders_filter_dialog_data' => $this->orders_filter_dialog_data,
        ], 200);
    }
}
