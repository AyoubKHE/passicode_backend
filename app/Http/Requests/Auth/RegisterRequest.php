<?php

namespace App\Http\Requests\Auth;

use Throwable;
use App\Models\Users\User;
use Illuminate\Validation\Rule;
use App\Models\Users\PhoneNumber;
use App\Models\Users\SocialMediaAccount;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Indicates if the validator should stop on the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = false;

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
            'first_name' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-ZÀ-ÿ]+$/u'
            ],


            'last_name' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-ZÀ-ÿ]+$/u'
            ],


            'email' => [
                'required',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    try {
                        $existing_user = User::where(
                            'email',
                            $value
                        )
                            ->first();
                    } catch (Throwable $th) {
                        $fail('An error occurred while accessing the database. Please try again later.');
                    }

                    if ($existing_user) {
                        $fail("The email has already been taken.");
                    }
                },
            ],


            'password' => [
                'required',
                'string',
                'min:1',
                // 'min:8',
                'max:50',
                // 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])$/'
            ],


            'role' => [
                'required',
                Rule::in(['Super Admin', 'Client'])
            ],            
        ];
    }
}
