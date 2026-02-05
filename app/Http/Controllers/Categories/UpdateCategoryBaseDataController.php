<?php

namespace App\Http\Controllers\Categories;

use Exception;
use Throwable;
use App\Models\Products\Category;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\UpdateCategoryBaseDataRequest;


class UpdateCategoryBaseDataController extends Controller
{
    private UpdateCategoryBaseDataRequest $global_request_object;
    private Category|null $requested_category;
    private Category|null $old_requested_category;
    private array $sent_inputs;


    private function updateBasicData()
    {
        $this->sent_inputs["updated_at"] = now();

        try {
            $is_updated = $this->requested_category
                ->update($this->sent_inputs);

            if (!$is_updated) {
                throw new Exception(
                    "- .",
                    500
                );
            }

        } catch (Throwable $th) {
            try {
                Log::channel('update_category_base_data_errors')->error(
                    "\n\n" .
                    "Description: Failed to update category's base data in database.\n\n" .
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

    private function checkModificationsAreMadeAndFilteringSentInputs()
    {
        $this->sent_inputs = $this->global_request_object->validated();

        $original_data = $this->requested_category->getOriginal();

        foreach ($this->sent_inputs as $key => $value) {
            if ($value == $original_data[$key]) {
                unset($this->sent_inputs[$key]);
            }
        }

        if (count($this->sent_inputs) === 0) {
            try {
                Log::channel('update_category_base_data_errors')->error(
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
                Log::channel('update_category_base_data_errors')->error(
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
                Log::channel('update_category_base_data_errors')->error(
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

            throw new Exception('Requested category not found.', 404);
        }

        $this->old_requested_category = clone $this->requested_category;
    }

    private function logRequest()
    {
        try {
            Log::channel('update_category_base_data_requests')->info(
                "\n\n" .
                "Description: Category's base data updated successfully.\n\n" .
                "Old Category Data: \n" .
                json_encode($this->old_requested_category->toArray(), JSON_PRETTY_PRINT) . "\n\n" .
                "Changes: \n" .
                json_encode($this->requested_category->getChanges(), JSON_PRETTY_PRINT) . "\n\n" .
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

    public function __invoke(UpdateCategoryBaseDataRequest $request)
    {
        $this->global_request_object = $request;

        $this->loadRequestedCategory();

        $this->checkModificationsAreMadeAndFilteringSentInputs();

        $this->updateBasicData();

        $this->logRequest();

        return response()->json([
            'message' => "Category's base data updated successfully.",
        ], 200);
    }
}
