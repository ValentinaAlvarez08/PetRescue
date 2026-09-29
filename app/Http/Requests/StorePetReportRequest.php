<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Sprint 2 — HU-13 / HU-14: validación del formulario guiado.
 * Todas las opciones cerradas (especie, raza, color, tamaño, sexo) se
 * validan contra config/pets.php, así nadie puede reportar algo que no
 * sea un animal doméstico aunque manipule el HTML.
 */
class StorePetReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Sin login: cualquier visitante puede reportar.
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Teléfono: solo dígitos (el usuario puede escribir "300 123 4567").
        if ($this->filled('contact_phone')) {
            $this->merge(['contact_phone' => preg_replace('/\D/', '', (string) $this->input('contact_phone'))]);
        }

        // Colores: llegan como arreglo de chips.
        if (is_string($this->input('colors'))) {
            $this->merge(['colors' => array_filter(explode(',', $this->input('colors')))]);
        }

        $this->merge(['contact_whatsapp' => $this->boolean('contact_whatsapp')]);
    }

    public function rules(): array
    {
        $species = array_keys(config('pets.species'));
        $breeds = config('pets.species.'.$this->input('species').'.breeds', []);
        $breeds[] = config('pets.unknown_breed');
        $bounds = config('pets.bounds');
        $usesSize = (bool) config('pets.species.'.$this->input('species').'.uses_size', false);

        return [
            'type' => ['required', Rule::in(['perdida', 'encontrada'])],
            'species' => ['required', Rule::in($species)],
            'breed' => [
                Rule::requiredIf($this->input('type') === 'perdida'),
                'nullable', Rule::in($breeds),
            ],
            'pet_name' => ['nullable', 'string', 'max:40', 'regex:/^[\pL\s\'\.-]+$/u'],
            'sex' => ['required', Rule::in(array_keys(config('pets.sexes')))],
            'size' => [Rule::requiredIf($usesSize), 'nullable', Rule::in(array_keys(config('pets.sizes')))],
            'colors' => ['required', 'array', 'min:1', 'max:'.config('pets.max_colors')],
            'colors.*' => [Rule::in(array_keys(config('pets.colors')))],
            'event_date' => [
                'required', 'date', 'before_or_equal:today',
                'after_or_equal:'.now()->subDays(config('pets.max_days_ago'))->toDateString(),
            ],
            'description' => ['required', 'string', 'min:15', 'max:500'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'contact_phone' => ['required', 'regex:/^3\d{9}$/'],
            'contact_whatsapp' => ['boolean'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'latitude' => ['required', 'numeric', 'between:'.$bounds['lat_min'].','.$bounds['lat_max']],
            'longitude' => ['required', 'numeric', 'between:'.$bounds['lng_min'].','.$bounds['lng_max']],
            'location_reference' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'species.required' => 'Elige qué animal es.',
            'species.in' => 'Solo se pueden reportar perros, gatos, conejos, aves o hámsteres/cobayos.',
            'breed.required' => 'Elige la raza (si no es de raza, elige "Criollo / mestizo").',
            'breed.in' => 'Elige una raza de la lista.',
            'pet_name.regex' => 'El nombre solo puede tener letras.',
            'sex.required' => 'Indica si es macho, hembra o si no sabes.',
            'size.required' => 'Elige el tamaño del perro.',
            'colors.required' => 'Elige al menos un color.',
            'colors.max' => 'Elige máximo :max colores (los principales).',
            'colors.*.in' => 'Elige colores de la lista.',
            'event_date.required' => 'Indica la fecha.',
            'event_date.before_or_equal' => 'La fecha no puede ser futura.',
            'event_date.after_or_equal' => 'Solo se aceptan reportes de los últimos '.config('pets.max_days_ago').' días.',
            'description.required' => 'Cuéntanos señas particulares (collar, manchas, cicatrices, comportamiento).',
            'description.min' => 'La descripción debe tener al menos :min caracteres.',
            'photo.required' => 'Adjunta una foto de la mascota.',
            'photo.image' => 'El archivo debe ser una imagen.',
            'photo.mimes' => 'La foto debe ser JPG, PNG o WEBP.',
            'photo.max' => 'La foto no puede pesar más de 5 MB.',
            'contact_phone.required' => 'Deja un número de celular para que puedan contactarte.',
            'contact_phone.regex' => 'Escribe un celular colombiano de 10 dígitos que empiece por 3.',
            'contact_email.email' => 'El correo no es válido.',
            'latitude.required' => 'Marca en el mapa el lugar.',
            'longitude.required' => 'Marca en el mapa el lugar.',
            'latitude.between' => 'El punto marcado debe estar dentro de Colombia.',
            'longitude.between' => 'El punto marcado debe estar dentro de Colombia.',
        ];
    }

    /** Datos listos para guardar en pet_reports. */
    public function reportData(): array
    {
        $data = $this->safe()->except(['colors', 'photo']);
        $data['color'] = implode(',', $this->validated('colors'));

        if (($data['breed'] ?? null) === config('pets.unknown_breed')) {
            $data['breed'] = null;
        }

        return $data;
    }
}
