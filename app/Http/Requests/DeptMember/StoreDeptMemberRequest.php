<?php

namespace App\Http\Requests\DeptMember;

use App\Enums\DeptMemberRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreDeptMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'role'    => ['required', DeptMemberRole::rule()],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Selecciona un usuario.',
            'user_id.exists'   => 'El usuario seleccionado no existe.',
            'role.required'    => 'El rol es obligatorio.',
            'role.in'          => 'Rol no válido.',
        ];
    }
}
