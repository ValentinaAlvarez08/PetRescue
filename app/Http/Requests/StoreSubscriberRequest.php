<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Sin login: cualquier visitante puede suscribirse a avisos cercanos.
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_km' => ['nullable', 'numeric', 'min:0.5', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Necesitamos tu correo para poder avisarte.',
            'latitude.required' => 'Selecciona la ubicación que quieres vigilar.',
            'longitude.required' => 'Selecciona la ubicación que quieres vigilar.',
        ];
    }
}
