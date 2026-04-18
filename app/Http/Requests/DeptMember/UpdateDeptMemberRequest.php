<?php

namespace App\Http\Requests\DeptMember;

use App\Enums\DeptMemberRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDeptMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', DeptMemberRole::rule()],
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'El rol es obligatorio.',
            'role.in'       => 'Rol no válido.',
        ];
    }
}
