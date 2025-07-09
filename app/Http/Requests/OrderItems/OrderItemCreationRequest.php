<?php

namespace App\Http\Requests\OrderItems;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class OrderItemCreationRequest extends FormRequest
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
            "order_id" => [
                'numeric',
                Rule::exists('orders', 'id')
            ],

            "product_id" => [
                'numeric',
                Rule::exists('products', 'id')
            ],
        ];

    }
}
