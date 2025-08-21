<?php

namespace App\Http\Controllers\Categories;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Models\Products\Category;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class getCategoryPriceController extends Controller
{
    private Request $global_request_object;
    private Category|null $requested_category;

    private function loadRequestedCategory()
    {
        try {

            $this->requested_category = Category::where(
                "id",
                $this->global_request_object->category_id
            )
                ->first();
        } catch (Throwable $th) {

            Log::channel('get_category_price_errors')->error(
                "\n\n" .
                "Description: Failed to get requested category from database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Category ID: " . $this->global_request_object->category_id . "\n\n" .
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

        if (!$this->requested_category) {

            Log::channel('get_category_price_errors')->error(
                "\n\n" .
                "Description: Requested category not found.\n\n" .
                "Error message: - .\n\n" .
                "Category ID: " . $this->global_request_object->category_id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'Requested category not found.',
                404
            );
        }
    }

    public function __invoke(Request $request)
    {
        $this->global_request_object = $request;

        $this->loadRequestedCategory();

        return response()->json([
            'category' => [
                'price' => $this->requested_category->price,
                'discount' => $this->requested_category->discount,
            ],
        ], 200);
    }
}
