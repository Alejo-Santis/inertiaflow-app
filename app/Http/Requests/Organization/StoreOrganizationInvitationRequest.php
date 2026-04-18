<?php

namespace App\Http\Requests\Organization;

use App\Enums\OrgMemberRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizationInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'role'  => ['required', OrgMemberRole::rule()],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email'    => 'Ingresa un correo electrónico válido.',
            'role.required'  => 'El rol es obligatorio.',
            'role.in'        => 'Rol no válido.',
        ];
    }
}
