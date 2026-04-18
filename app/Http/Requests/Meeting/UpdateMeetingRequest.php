<?php

namespace App\Http\Requests\Meeting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMeetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'meeting_url'      => ['nullable', 'url'],
            'platform'         => ['nullable', 'string', 'in:zoom,meet,teams,other'],
            'scheduled_at'     => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'project_id'       => ['nullable', 'exists:projects,id'],
            'participants'     => ['nullable', 'array'],
            'participants.*'   => ['integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'            => 'El título es obligatorio.',
            'scheduled_at.required'     => 'La fecha y hora son obligatorias.',
            'duration_minutes.required' => 'La duración es obligatoria.',
            'platform.in'               => 'Plataforma no válida.',
        ];
    }
}
