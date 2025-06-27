<?php

namespace App\Http\Requests\Products;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use App\Models\Products\Product;
use Illuminate\Foundation\Http\FormRequest;

class ProductsCreationRequest extends FormRequest
{

    protected array $available_codes;

    protected function prepareForValidation()
    {
        $this->available_codes = Product::pluck('code')->toArray();
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
            'products' => [
                'required',
                'array',
                'min:1',
                'max:10',
            ],

            'products.*.code' => [
                'required',
                'distinct',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    foreach ($this->available_codes as $available_code) {
                        if (Crypt::decryptString($available_code) === $value) {
                            $fail("This code already exists in the database: '{$value}'");
                        }
                    }
                },
            ],

            'products.*.expiration_date' => [
                'nullable',
                'date',
                'after:today'
            ],

            "products.*.purchase_price" => [
                "required",
                "numeric",
                "min:0",
                "max:99999999.99"
            ],

            'related_category_id' => [
                'required',
                'numeric',
                Rule::exists('categories', 'id'),
            ],

            "supplier" => [
                "required",
                "string",
                "max:255",
            ],
        ];
    }
}
