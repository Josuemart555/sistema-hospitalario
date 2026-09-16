<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('opciones.administrar') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $optionId = $this->route('option')?->id;

        return [
            'name' => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'integer', 'exists:options,id', 'not_in:'.$optionId],
            'permission_id' => ['nullable', 'integer', 'exists:permissions,id'],
            'route_name' => ['nullable', 'string', 'max:150'],
            'icon' => ['required', 'string', 'max:80', 'regex:/^bi-[a-z0-9-]+$/'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
