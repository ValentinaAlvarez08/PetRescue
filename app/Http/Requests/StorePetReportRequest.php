<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePetReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Sin login: cualquier visitante puede reportar.
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:perdida,encontrada'],
            'pet_name' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'contact_phone' => ['required', 'string', 'max:30'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'location_reference' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'Describe a la mascota (raza, color, señas).',
            'contact_phone.required' => 'Deja un número de contacto para que puedan escribirte.',
            'latitude.required' => 'Selecciona una ubicación en el mapa.',
            'longitude.required' => 'Selecciona una ubicación en el mapa.',
        ];
    }
}
