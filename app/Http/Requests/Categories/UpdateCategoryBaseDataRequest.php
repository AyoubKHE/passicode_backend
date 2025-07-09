<?php

namespace App\Http\Requests\Categories;

use Throwable;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Products\Category;

class UpdateCategoryBaseDataRequest extends FormRequest
{

    /**
     * Indicates if the validator should stop on the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name" => [
                "string",
                "min:5",
                "max:255",
                function ($attribute, $value, $fail) {
                    try {
                        $existing_category = Category::where(
                            'name',
                            $value
                        )->where(
                                'id',
                                '!=',
                                $this->category_id
                            )->first();
                    } catch (Throwable $th) {
                        $fail('An error occurred while accessing the database. Please try again later.');
                    }

                    if ($existing_category) {
                        $fail("The name has already been taken.");
                    }
                },
            ],


            "description" => [
                "string",
                "min:5",
                "max:65535",
                function ($attribute, $value, $fail) {
                    try {
                        $existing_category = Category::where(
                            'description',
                            $value
                        )->where(
                                'id',
                                '!=',
                                $this->category_id
                            )->first();
                    } catch (Throwable $th) {
                        $fail('An error occurred while accessing the database. Please try again later.');
                    }

                    if ($existing_category) {
                        $fail("The description has already been taken.");
                    }
                },
            ],


            "price" => [
                "nullable",
                "numeric",
                "min:0",
                "max:99999999.99"
            ],


            "discount" => [
                "nullable",
                "integer",
                "min:0",
                "max:100"
            ],


            "is_active" => [
                "boolean"
            ],


            'quantity' => [
                "integer",
                "min:0",
                "max:99999999",
            ],
        ];

    }
}
