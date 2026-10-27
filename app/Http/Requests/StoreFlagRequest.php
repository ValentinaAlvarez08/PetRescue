<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Sprint 4 — HU-20: denuncia de contenido inapropiado.
 */
class StoreFlagRequest extends FormRequest
{
    /** El formulario vive en una ventana desplegable; los errores se muestran en su propio bloque. */
    protected $errorBag = 'flag';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['details' => trim((string) $this->input('details')) ?: null]);
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', Rule::in(array_keys(config('community.flags.reasons')))],
            'details' => [Rule::requiredIf($this->input('reason') === 'otro'), 'nullable', 'string', 'max:300'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Elige por qué es inapropiado.',
            'reason.in' => 'Elige un motivo de la lista.',
            'details.required' => 'Cuéntanos brevemente el motivo.',
            'details.max' => 'El detalle puede tener máximo :max caracteres.',
        ];
    }
}
