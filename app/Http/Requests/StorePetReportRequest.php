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
            'species' => ['required', 'string', 'max:50'],
            'breed' => ['required_if:type,perdida', 'nullable', 'string', 'max:100'],
            'color' => ['required', 'string', 'max:100'],
            'size' => ['required_if:type,encontrada', 'nullable', 'string', 'max:20'],
            'description' => ['required', 'string', 'max:1000'],
            'photo' => ['required', 'image', 'max:4096'],
            'contact_phone' => ['required_without:contact_email', 'nullable', 'string', 'max:30'],
            'contact_email' => ['required_without:contact_phone', 'nullable', 'email', 'max:150'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'location_reference' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'species.required' => 'Indica la especie de la mascota.',
            'breed.required_if' => 'Indica la raza de la mascota.',
            'color.required' => 'Indica el color de la mascota.',
            'size.required_if' => 'Indica el tamaño de la mascota.',
            'description.required' => 'Describe a la mascota (señas particulares).',
            'photo.required' => 'Adjunta al menos una foto de la mascota.',
            'contact_phone.required_without' => 'Deja un número de contacto o un correo para que puedan escribirte.',
            'contact_email.required_without' => 'Deja un correo o un número de contacto para que puedan escribirte.',
            'latitude.required' => 'Selecciona una ubicación en el mapa.',
            'longitude.required' => 'Selecciona una ubicación en el mapa.',
        ];
    }
}
