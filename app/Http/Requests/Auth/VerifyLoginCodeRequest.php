<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VerifyLoginCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['code' => ['required', 'digits:6']];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Ingrese el código de 6 dígitos.',
            'code.digits' => 'El código debe tener 6 dígitos.',
        ];
    }
}
