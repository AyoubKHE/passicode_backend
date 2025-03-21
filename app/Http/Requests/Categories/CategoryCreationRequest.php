<?php

namespace App\Http\Requests\Categories;

use Throwable;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Products\Category;

class CategoryCreationRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge(json_decode($this->data, true, 512, JSON_THROW_ON_ERROR));
    }

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
                "required",
                "string",
                "min:5",
                "max:255",
                function ($attribute, $value, $fail) {
                    try {
                        $existing_category = Category::where(
                                'name',
                                $value
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
                "required",
                "string",
                "min:5",
                "max:65535",
                function ($attribute, $value, $fail) {
                    try {
                        $existing_category = Category::where(
                                'description',
                                $value
                            )->first();
                    } catch (Throwable $th) {
                        $fail('An error occurred while accessing the database. Please try again later.');
                    }

                    if ($existing_category) {
                        $fail("The description has already been taken.");
                    }
                },
            ],


            "is_active" => [
                "required",
                "boolean"
            ],


            "parent_id" => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
            ],


            'image' => [
                'required',
                'image',
                'mimes:jpg,png,jpeg,svg',
                'max:5000'
            ],

        ];

    }
}
