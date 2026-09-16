<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('usuarios.administrar') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'status' => ['required', 'in:invited,active,suspended'],
            'two_factor_allowed' => ['sometimes', 'boolean'],
            'roles' => ['array'], 'roles.*' => ['integer', 'exists:roles,id'],
            'permissions' => ['array'], 'permissions.*' => ['integer', 'exists:permissions,id'],
            'options' => ['array'], 'options.*' => ['integer', 'exists:options,id'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
