<?php

namespace App\Http\Controllers\Products;

use Exception;
use Throwable;
use App\Models\Products\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\Products\UpdateProductBaseDataRequest;


class UpdateProductBaseDataController extends Controller
{
    private UpdateProductBaseDataRequest $global_request_object;
    private Product|null $requested_product;
    private Product|null $old_requested_product;
    private array $sent_inputs;


    private function updateBasicData()
    {
        if (array_key_exists('code', $this->sent_inputs)) {

            $this->sent_inputs['code_start'] = substr($this->sent_inputs['code'], 0, 5);

            $this->sent_inputs["code"] = Crypt::encryptString($this->sent_inputs["code"]);
        }

        if (array_key_exists('expiration_date', $this->sent_inputs)) {

            if (!$this->sent_inputs['expiration_date']) {
                $this->sent_inputs['expiration_date'] = "9999-12-31";
            }
        }

        $this->sent_inputs["updated_at"] = now();

        try {
            $is_updated = $this->requested_product
                ->update($this->sent_inputs);

            if (!$is_updated) {
                throw new Exception(
                    "- .",
                    500
                );
            }
        } catch (Throwable $th) {

            Log::channel('update_product_base_data_errors')->error(
                "\n\n" .
                "Description: Failed to update requested product from database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
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

    private function checkModificationsAreMadeAndFilteringSentInputs()
    {
        $this->sent_inputs = $this->global_request_object->validated();

        $original_data = $this->requested_product->getOriginal();

        foreach ($this->sent_inputs as $key => $value) {
            if ($key === "code") {
                if ($value === Crypt::decryptString($original_data[$key])) {
                    unset($this->sent_inputs[$key]);
                }
            } else {
                if ($value == $original_data[$key]) {
                    unset($this->sent_inputs[$key]);
                }
            }

        }

        if (count($this->sent_inputs) === 0) {

            Log::channel('update_product_base_data_errors')->error(
                "\n\n" .
                "Description: No updates were made. Please ensure there is at least one modification before submitting.\n\n" .
                "Error message: - .\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'No updates were made. Please ensure there is at least one modification before submitting.',
                400
            );
        }
    }

    private function loadRequestedProduct()
    {
        try {

            $this->requested_product = Product::where(
                "id",
                $this->global_request_object->product_id
            )
                ->lockForUpdate()
                ->first();
        } catch (Throwable $th) {

            Log::channel('update_product_base_data_errors')->error(
                "\n\n" .
                "Description: Failed to get requested product from database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
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

        if (!$this->requested_product) {

            Log::channel('update_product_base_data_errors')->error(
                "\n\n" .
                "Description: Requested product not found.\n\n" .
                "Error message: - .\n\n" .
                "Product ID: " . $this->global_request_object->product_id . "\n\n" .
                "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'Requested product not found.',
                404
            );
        }

        $this->old_requested_product = clone $this->requested_product;
    }

    private function logRequest()
    {
        try {
            Log::channel('update_product_base_data_requests')->info(
                "\n\n" .
                "Description: Product's base data updated successfully.\n\n" .
                "Old Product Data: \n" .
                json_encode($this->old_requested_product->toArray(), JSON_PRETTY_PRINT) . "\n\n" .
                "Changes: \n" .
                json_encode($this->requested_product->getChanges(), JSON_PRETTY_PRINT) . "\n\n" .
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

    public function __invoke(UpdateProductBaseDataRequest $request)
    {
        $this->global_request_object = $request;

        DB::transaction(function () {
            $this->loadRequestedProduct();

            $this->checkModificationsAreMadeAndFilteringSentInputs();

            $this->updateBasicData();
        });

        $this->logRequest();

        return response()->json([
            'message' => "Product's base data updated successfully.",
        ], 200);
    }
}
