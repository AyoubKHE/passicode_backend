<?php

namespace App\Http\Requests\Categories;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class GetPaginatedCategoriesByFilterRequest extends FormRequest
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

            'id' => [
                'numeric',
            ],


            'name' => [
                "string",
                "max:255"
            ],


            'price' => [
                'array',
                'min:1'
            ],
            'price.from' => [
                "numeric",
                "min:0",
                "max:99999999.99",
                function ($attribute, $value, $fail) {
                    if ($this->input('price.to') !== null && $value > $this->input('price.to')) {
                        $fail('The price.from must be less than or equal price.to.');
                    }
                }
            ],
            'price.to' => [
                "numeric",
                "min:0",
                "max:99999999.99",
            ],


            'discount' => [
                'array',
                'min:1'
            ],
            'discount.from' => [
                "numeric",
                "min:0",
                "max:100",
                function ($attribute, $value, $fail) {
                    if ($this->input('discount.to') !== null && $value > $this->input('discount.to')) {
                        $fail('The discount.from must be less than or equal discount.to.');
                    }
                }
            ],
            'discount.to' => [
                "numeric",
                "min:0",
                "max:99999999",
            ],


            'quantity' => [
                'array',
                'min:1'
            ],
            'quantity.from' => [
                "integer",
                "min:0",
                "max:99999999",
                function ($attribute, $value, $fail) {
                    if ($this->input('quantity.to') !== null && $value > $this->input('quantity.to')) {
                        $fail('The quantity.from must be less than or equal quantity.to.');
                    }
                }
            ],
            'quantity.to' => [
                "integer",
                "min:0",
                "max:99999999",
            ],


            'is_active' => [
                'boolean'
            ],


            'is_leaf_category' => [
                'boolean'
            ],


            'parent_category_id' => [
                'nullable',
                'numeric',
                Rule::exists('categories', 'id'),
            ],


            'created_at' => [
                'array',
                'min:1'
            ],
            'created_at.from' => [
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) {
                    if ($this->input('created_at.to') !== null && $value > $this->input('created_at.to')) {
                        $fail('The created_at.from must be a date before or equal created_at.to.');
                    }
                }
            ],
            'created_at.to' => [
                'date_format:Y-m-d'
            ],


            'updated_at' => [
                'nullable',
                'array',
                'min:1'
            ],
            'updated_at.from' => [
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) {
                    if ($this->input('updated_at.to') !== null && $value > $this->input('updated_at.to')) {
                        $fail('The updated_at.from must be a date before or equal updated_at.to.');
                    }
                }
            ],
            'updated_at.to' => [
                'date_format:Y-m-d'
            ],
        ];

    }
}
