<?php

namespace App\Http\Requests\Products;

use App\Models\Products\Product;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductBaseDataRequest extends FormRequest
{

    protected array $available_codes;

    protected function prepareForValidation()
    {
        $this->available_codes = Product::orderBy('id')
            ->pluck('code', 'id')
            ->toArray();
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
            "code" => [
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    foreach ($this->available_codes as $id => $code) {

                        if ($id !== (int) $this->product_id) {
                            if (Crypt::decryptString($code) === $value) {
                                $fail("This code already exists in the database: '{$value}'");
                            }
                        }
                    }
                },
            ],


            "expiration_date" => [
                'nullable',
                'date',
                'after:today'
            ],


            "purchase_price" => [
                "numeric",
                "min:0",
                "max:99999999.99"
            ],
        ];

    }
}
