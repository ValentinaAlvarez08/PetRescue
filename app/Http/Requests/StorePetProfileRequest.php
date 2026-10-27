<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Sprint 4 — HU-10: validación del perfil digital (al crearlo y al editarlo
 * con el enlace privado). Usa los mismos catálogos de config/pets.php que
 * los reportes, para poder prellenar un reporte de pérdida desde el perfil.
 */
class StorePetProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Sin login: cualquier visitante puede crear el perfil de su mascota.
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('contact_phone')) {
            $this->merge(['contact_phone' => preg_replace('/\D/', '', (string) $this->input('contact_phone'))]);
        }

        $this->merge(['contact_whatsapp' => $this->boolean('contact_whatsapp')]);
    }

    public function rules(): array
    {
        $breeds = config('pets.species.'.$this->input('species').'.breeds', []);
        $usesSize = (bool) config('pets.species.'.$this->input('species').'.uses_size', false);
        // Al editar, la foto actual se conserva si no se sube otra.
        $editing = $this->route('token') !== null;

        return [
            'name' => ['required', 'string', 'max:40', 'regex:/^[\pL\s\'\.-]+$/u'],
            'species' => ['required', Rule::in(array_keys(config('pets.species')))],
            'breed' => ['required', Rule::in($breeds)],
            'sex' => ['required', Rule::in(['macho', 'hembra'])],
            'size' => [Rule::requiredIf($usesSize), 'nullable', Rule::in(array_keys(config('pets.sizes')))],
            'colors' => ['required', 'array', 'min:1', 'max:'.config('pets.max_colors')],
            'colors.*' => [Rule::in(array_keys(config('pets.colors')))],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today', 'after:'.now()->subYears(40)->toDateString()],
            'photo' => [$editing ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'distinctive_marks' => ['nullable', 'string', 'max:500'],
            'sterilized' => ['required', Rule::in(array_keys(config('pets.sterilized')))],
            'vaccines' => ['nullable', 'string', 'max:300'],
            'health_notes' => ['nullable', 'string', 'max:500'],
            'owner_name' => ['required', 'string', 'max:80'],
            'contact_phone' => ['required', 'regex:/^3\d{9}$/'],
            'contact_whatsapp' => ['boolean'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'neighborhood' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Escribe el nombre de tu mascota.',
            'name.regex' => 'El nombre solo puede tener letras.',
            'species.required' => 'Elige qué animal es.',
            'species.in' => 'Solo se pueden registrar perros, gatos, conejos, aves o hámsteres/cobayos.',
            'breed.required' => 'Elige la raza (si no es de raza, elige "Criollo / mestizo").',
            'breed.in' => 'Elige una raza de la lista.',
            'sex.required' => 'Indica si es macho o hembra.',
            'size.required' => 'Elige el tamaño del perro.',
            'colors.required' => 'Elige al menos un color.',
            'colors.max' => 'Elige máximo :max colores (los principales).',
            'birth_date.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'birth_date.after' => 'Revisa la fecha de nacimiento.',
            'photo.required' => 'Sube una foto donde se vea bien tu mascota.',
            'photo.image' => 'El archivo debe ser una imagen.',
            'photo.mimes' => 'La foto debe ser JPG, PNG o WEBP.',
            'photo.max' => 'La foto no puede pesar más de 5 MB.',
            'sterilized.required' => 'Indica si está esterilizada.',
            'owner_name.required' => 'Escribe tu nombre para que sepan a quién contactar.',
            'contact_phone.required' => 'Deja un número de celular para que puedan contactarte.',
            'contact_phone.regex' => 'Escribe un celular colombiano de 10 dígitos que empiece por 3.',
            'contact_email.email' => 'El correo no es válido.',
        ];
    }

    /** Datos listos para guardar en pet_profiles. */
    public function profileData(): array
    {
        $data = $this->safe()->except(['colors', 'photo']);
        $data['color'] = implode(',', $this->validated('colors'));

        return $data;
    }
}
