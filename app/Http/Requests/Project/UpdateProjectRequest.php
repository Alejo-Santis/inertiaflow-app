<?php

namespace App\Http\Requests\Project;

use App\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date', 'after_or_equal:start_date'],
            'status'          => ['required', 'string', ProjectStatus::rule()],
            'color'           => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'priority'        => ['required', 'string', 'in:low,medium,high'],
            'deadline'        => ['nullable', 'date'],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'department_id'   => ['nullable', 'exists:departments,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => 'El nombre es obligatorio.',
            'status.required' => 'El estado es obligatorio.',
            'status.in'       => 'Estado no válido.',
            'color.required'  => 'El color es obligatorio.',
            'color.regex'     => 'El color debe ser un código hexadecimal válido.',
            'priority.in'     => 'La prioridad debe ser low, medium o high.',
        ];
    }
}
