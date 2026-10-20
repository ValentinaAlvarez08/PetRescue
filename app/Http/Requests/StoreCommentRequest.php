<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Sprint 3 — HU-18: validación de los comentarios de una publicación.
 */
class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Sin login: cualquier visitante puede comentar.
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nickname' => trim((string) $this->input('nickname')),
            'body' => trim((string) $this->input('body')),
        ]);
    }

    public function rules(): array
    {
        return [
            'nickname' => ['required', 'string', 'max:40'],
            'body' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nickname.required' => 'Escribe un apodo para comentar.',
            'nickname.max' => 'El apodo puede tener máximo :max caracteres.',
            'body.required' => 'Escribe tu comentario.',
            'body.min' => 'El comentario debe tener al menos :min caracteres.',
            'body.max' => 'El comentario puede tener máximo :max caracteres.',
        ];
    }
}
