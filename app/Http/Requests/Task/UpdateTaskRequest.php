<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'           => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'priority'        => ['required', 'integer', 'between:1,4'],
            'due_date'        => ['nullable', 'date'],
            'status'          => ['required', 'string', TaskStatus::rule()],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'meeting_url'     => ['nullable', 'url'],
            'assignees'       => ['nullable', 'array'],
            'assignees.*'     => ['integer', 'exists:users,id'],
            'label_ids'       => ['nullable', 'array'],
            'label_ids.*'     => ['integer', 'exists:labels,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'  => 'El título es obligatorio.',
            'status.required' => 'El estado es obligatorio.',
            'status.in'       => 'Estado no válido.',
        ];
    }
}
