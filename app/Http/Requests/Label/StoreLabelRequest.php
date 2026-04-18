<?php

namespace App\Http\Requests\Label;

use Illuminate\Foundation\Http\FormRequest;

class StoreLabelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'El nombre de la etiqueta es obligatorio.',
            'color.required' => 'El color es obligatorio.',
            'color.regex'    => 'El color debe ser un código hexadecimal válido.',
        ];
    }
}
