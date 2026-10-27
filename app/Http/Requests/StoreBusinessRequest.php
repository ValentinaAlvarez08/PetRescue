<?php

namespace App\Http\Requests;

use App\Models\Business;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Sprint 4 — HU-19: solicitud para aparecer en el directorio de servicios.
 */
class StoreBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Sin login: cualquier negocio puede pedir que lo agreguen.
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('phone')) {
            $this->merge(['phone' => preg_replace('/\D/', '', (string) $this->input('phone'))]);
        }

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'has_whatsapp' => $this->boolean('has_whatsapp'),
        ]);
    }

    public function rules(): array
    {
        $bounds = config('pets.bounds');

        return [
            'name' => ['required', 'string', 'max:120'],
            'category' => ['required', Rule::in(array_keys(config('community.directory')))],
            'description' => ['required', 'string', 'min:20', 'max:500'],
            'services' => ['nullable', 'string', 'max:300'],
            // Celular (3xx) o fijo con indicativo (60x), 10 dígitos.
            'phone' => ['required', 'regex:/^(3\d{9}|60\d{8})$/'],
            'has_whatsapp' => ['boolean'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['required', 'string', 'max:200'],
            'schedule' => ['nullable', 'string', 'max:150'],
            'latitude' => ['required', 'numeric', 'between:'.$bounds['lat_min'].','.$bounds['lat_max']],
            'longitude' => ['required', 'numeric', 'between:'.$bounds['lng_min'].','.$bounds['lng_max']],
            'requester_name' => ['required', 'string', 'max:80'],
            'requester_email' => ['required', 'email', 'max:150'],
            'authorized' => ['accepted'],
        ];
    }

    /** CID 4: no se repite un negocio que ya está en el directorio o en revisión. */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $exists = Business::whereRaw('lower(name) = ?', [mb_strtolower($this->input('name'))])
                    ->where('review_status', '!=', 'rechazado')
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('name', 'Ya hay un negocio con ese nombre en el directorio o en revisión.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Escribe el nombre del negocio.',
            'category.required' => 'Elige a qué se dedica el negocio.',
            'category.in' => 'Elige una categoría de la lista.',
            'description.required' => 'Cuéntanos a qué se dedica el negocio.',
            'description.min' => 'La descripción debe tener al menos :min caracteres.',
            'phone.required' => 'Deja un teléfono de contacto del negocio.',
            'phone.regex' => 'Escribe un celular (3xx) o un fijo con indicativo (60x) de 10 dígitos.',
            'email.email' => 'El correo del negocio no es válido.',
            'address.required' => 'Escribe la dirección del negocio.',
            'latitude.required' => 'Marca en el mapa dónde queda el negocio.',
            'longitude.required' => 'Marca en el mapa dónde queda el negocio.',
            'latitude.between' => 'El punto marcado debe estar dentro de Colombia.',
            'longitude.between' => 'El punto marcado debe estar dentro de Colombia.',
            'requester_name.required' => 'Escribe tu nombre.',
            'requester_email.required' => 'Escribe tu correo: por ahí te contamos si aprobamos la solicitud.',
            'requester_email.email' => 'Tu correo no es válido.',
            'authorized.accepted' => 'Confirma que eres el dueño o tienes autorización para registrar el negocio.',
        ];
    }

    /** Datos listos para guardar en businesses (queda pendiente de revisión). */
    public function businessData(): array
    {
        return $this->safe()->except(['authorized']) + [
            'is_active' => false,
            'review_status' => 'pendiente',
        ];
    }
}
