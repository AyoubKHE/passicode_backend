<?php

namespace App\Http\Requests\Orders;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class GetPaginatedOrdersByFilterRequest extends FormRequest
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


            'public_id' => [
                "string",
                "max:255"
            ],


            'user_id' => [
                'numeric',
                Rule::exists('users', 'id'),
            ],


            'category_id' => [
                'numeric',
                Rule::exists('categories', 'id'),
            ],


            'status' => [
                Rule::in(["pending", "paid", "failed", "canceled", "expired"])
            ],


            'amount' => [
                'array',
                'min:1'
            ],
            'amount.from' => [
                "numeric",
                "min:0",
                "max:99999999.99",
                function ($attribute, $value, $fail) {
                    if ($this->input('amount.to') !== null && $value > $this->input('amount.to')) {
                        $fail('The amount.from must be less than or equal amount.to.');
                    }
                }
            ],
            'amount.to' => [
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
        ];

    }
}
