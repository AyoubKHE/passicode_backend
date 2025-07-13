<?php

namespace App\Http\Requests\Orders;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderBaseDataRequest extends FormRequest
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
            'quantity' => [
                "integer",
                "min:1",
                "max:99999999",
            ],


            'status' => [
                Rule::in(["pending", "processing", "completed", "failed", "under_review", "partially_refunded", "refunded"])
            ],


            'payment_status' => [
                Rule::in(["pending", "paid", "failed", "canceled", "expired"])
            ],


            'amount' => [
                "numeric",
                "min:0",
                "max:99999999.99",
            ],


            "more_informations" => [
                "nullable",
                "string",
                "max:65535",
            ],
        ];

    }
}
