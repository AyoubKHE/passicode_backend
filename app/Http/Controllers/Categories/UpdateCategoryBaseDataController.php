<?php

namespace App\Http\Controllers\Categories;

use Exception;
use Throwable;
use App\Models\Products\Category;
use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\UpdateCategoryBaseDataRequest;


class UpdateCategoryBaseDataController extends Controller
{
    private UpdateCategoryBaseDataRequest $global_request_object;
    private Category|null $requested_category;
    private array $sent_inputs;


    private function updateBasicData()
    {
        $this->sent_inputs["updated_at"] = now();

        try {
            $is_updated = $this->requested_category
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

        $original_data = $this->requested_category->getOriginal();

        foreach ($this->sent_inputs as $key => $value) {
            if ($value == $original_data[$key]) {
                unset($this->sent_inputs[$key]);
            }
        }

        if (count($this->sent_inputs) === 0) {
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
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$this->requested_category) {
            throw new Exception('Requested category not found.', 404);
        }
    }


    public function __invoke(UpdateCategoryBaseDataRequest $request)
    {
        $this->global_request_object = $request;

        $this->loadRequestedCategory();

        $this->checkModificationsAreMadeAndFilteringSentInputs();

        $this->updateBasicData();

        return response()->json([
            'message' => "Category's base data updated successfully.",
        ], 200);
    }
}
