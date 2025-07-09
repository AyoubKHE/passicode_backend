<?php

namespace App\Http\Requests\FailedQuantityRequests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class GetPaginatedFailedQuantityRequestsByFilterRequest extends FormRequest
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


            'user_id' => [
                'numeric',
                Rule::exists('users', 'id'),
            ],


            'category_id' => [
                'numeric',
                Rule::exists('categories', 'id'),
            ],


            'status' => [
                Rule::in(["not_settled", "settled"])
            ],


            "is_category_active" => [
                "boolean"
            ],


            'available_quantity' => [
                'array',
                'min:1'
            ],
            'available_quantity.from' => [
                "numeric",
                "min:0",
                "max:99999999.99",
                function ($attribute, $value, $fail) {
                    if ($this->input('available_quantity.to') !== null && $value > $this->input('available_quantity.to')) {
                        $fail('The available_quantity.from must be less than or equal available_quantity.to.');
                    }
                }
            ],
            'available_quantity.to' => [
                "numeric",
                "min:0",
                "max:99999999.99",
            ],


            'requested_quantity' => [
                'array',
                'min:1'
            ],
            'requested_quantity.from' => [
                "numeric",
                "min:0",
                "max:99999999.99",
                function ($attribute, $value, $fail) {
                    if ($this->input('requested_quantity.to') !== null && $value > $this->input('requested_quantity.to')) {
                        $fail('The requested_quantity.from must be less than or equal requested_quantity.to.');
                    }
                }
            ],
            'requested_quantity.to' => [
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
