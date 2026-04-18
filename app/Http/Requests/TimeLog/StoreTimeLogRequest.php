<?php

namespace App\Http\Requests\TimeLog;

use Illuminate\Foundation\Http\FormRequest;

class StoreTimeLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hours'       => ['required', 'numeric', 'min:0.25', 'max:24'],
            'description' => ['nullable', 'string', 'max:255'],
            'logged_date' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'hours.required'    => 'Las horas son obligatorias.',
            'hours.numeric'     => 'Las horas deben ser un número.',
            'hours.min'         => 'El mínimo es 0.25 horas (15 minutos).',
            'hours.max'         => 'El máximo es 24 horas.',
            'logged_date.required' => 'La fecha es obligatoria.',
        ];
    }
}
