<?php

namespace App\Http\Requests\Products;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class GetPaginatedProductsByFilterRequest extends FormRequest
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


            'code' => [
                "string",
                "max:255"
            ],


            'related_category_id' => [
                'integer',
                Rule::exists('categories', 'id'),
            ],


            'sold' => [
                'boolean'
            ],


            'expiration_date' => [
                'nullable',
                'array',
                'min:1'
            ],
            'expiration_date.from' => [
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) {
                    if ($this->input('expiration_date.to') !== null && $value > $this->input('expiration_date.to')) {
                        $fail('The expiration_date.from must be a date before or equal expiration_date.to.');
                    }
                }
            ],
            'expiration_date.to' => [
                'date_format:Y-m-d'
            ],


            'purchase_price' => [
                'array',
                'min:1'
            ],
            'purchase_price.from' => [
                "numeric",
                "min:0",
                "max:99999999.99",
                function ($attribute, $value, $fail) {
                    if ($this->input('purchase_price.to') !== null && $value > $this->input('purchase_price.to')) {
                        $fail('The purchase_price.from must be less than or equal purchase_price.to.');
                    }
                }
            ],
            'purchase_price.to' => [
                "numeric",
                "min:0",
                "max:99999999.99",
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
