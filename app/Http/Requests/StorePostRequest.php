<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Sprint 3 — HU-17: validación al publicar (y al editar con el enlace
 * privado) en un espacio de la comunidad.
 */
class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Sin login: cualquier visitante puede publicar.
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nickname' => trim((string) $this->input('nickname')),
            'title' => trim((string) $this->input('title')),
            'body' => trim((string) $this->input('body')),
            'remove_photo' => $this->boolean('remove_photo'),
        ]);
    }

    public function rules(): array
    {
        return [
            'nickname' => ['required', 'string', 'max:40'],
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'min:20', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_photo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nickname.required' => 'Escribe un apodo para que la comunidad sepa quién publica.',
            'nickname.max' => 'El apodo puede tener máximo :max caracteres.',
            'title.required' => 'Escribe un título para tu publicación.',
            'title.max' => 'El título puede tener máximo :max caracteres.',
            'body.required' => 'Escribe el texto de tu publicación.',
            'body.min' => 'El texto debe tener al menos :min caracteres.',
            'body.max' => 'El texto puede tener máximo :max caracteres.',
            'photo.image' => 'El archivo debe ser una imagen.',
            'photo.mimes' => 'La foto debe ser JPG, PNG o WEBP.',
            'photo.max' => 'La foto no puede pesar más de 5 MB.',
        ];
    }

    /** Datos listos para guardar en posts. */
    public function postData(): array
    {
        return $this->safe()->only(['nickname', 'title', 'body']);
    }
}
