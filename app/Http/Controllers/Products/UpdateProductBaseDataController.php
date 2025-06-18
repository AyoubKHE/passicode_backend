<?php

namespace App\Http\Controllers\Products;

use Exception;
use Throwable;
use App\Models\Products\Product;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\Products\UpdateProductBaseDataRequest;


class UpdateProductBaseDataController extends Controller
{
    private UpdateProductBaseDataRequest $global_request_object;
    private Product|null $requested_product;
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
            )->first();
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

    public function __invoke(UpdateProductBaseDataRequest $request)
    {
        $this->global_request_object = $request;

        $this->loadRequestedProduct();

        $this->checkModificationsAreMadeAndFilteringSentInputs();

        $this->updateBasicData();

        return response()->json([
            'message' => "Product's base data updated successfully.",
        ], 200);
    }
}
