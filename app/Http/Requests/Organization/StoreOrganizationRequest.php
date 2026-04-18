<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // acceso ya garantizado por middleware auth
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'nit'         => ['required', 'string', 'max:20', 'unique:organizations,nit'],
            'dv'          => ['required', 'string', 'max:1'],
            'description' => ['nullable', 'string'],
            'color'       => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la organización es obligatorio.',
            'nit.required'  => 'El NIT es obligatorio.',
            'nit.unique'    => 'Ya existe una organización con este NIT.',
            'dv.required'   => 'El dígito de verificación es obligatorio.',
        ];
    }
}
