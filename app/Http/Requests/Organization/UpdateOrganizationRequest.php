<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // autorización de negocio la maneja OrganizationController::authorizeEdit()
    }

    public function rules(): array
    {
        $orgId = $this->route('organization')?->id;

        return [
            'name'        => ['required', 'string', 'max:255'],
            'nit'         => ['required', 'string', 'max:20', Rule::unique('organizations', 'nit')->ignore($orgId)],
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
            'nit.unique'    => 'Ya existe otra organización con este NIT.',
            'dv.required'   => 'El dígito de verificación es obligatorio.',
        ];
    }
}
