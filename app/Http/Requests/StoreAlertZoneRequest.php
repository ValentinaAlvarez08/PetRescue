<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Sprint 4 — HU-11 (CID 1): datos de una zona vigilada. Se usa al agregar
 * una zona desde el enlace de administración.
 */
class StoreAlertZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Sin login: el acceso lo da el enlace con el token.
        return true;
    }

    public function rules(): array
    {
        $bounds = config('pets.bounds');
        $alerts = config('pets.alerts');

        return [
            'label' => ['required', 'string', 'max:40'],
            'latitude' => ['required', 'numeric', 'between:'.$bounds['lat_min'].','.$bounds['lat_max']],
            'longitude' => ['required', 'numeric', 'between:'.$bounds['lng_min'].','.$bounds['lng_max']],
            'radius_km' => ['required', 'integer', 'between:'.$alerts['radius_min'].','.$alerts['radius_max']],
        ];
    }

    public function messages(): array
    {
        $alerts = config('pets.alerts');

        return [
            'label.required' => 'Ponle un nombre a la zona (ej: Casa, Trabajo, Finca).',
            'label.max' => 'El nombre de la zona no puede pasar de 40 caracteres.',
            'latitude.required' => 'Marca en el mapa la zona que quieres vigilar.',
            'longitude.required' => 'Marca en el mapa la zona que quieres vigilar.',
            'latitude.between' => 'La zona debe estar dentro de Colombia.',
            'longitude.between' => 'La zona debe estar dentro de Colombia.',
            'radius_km.required' => 'Elige el radio de la zona.',
            'radius_km.integer' => "El radio debe ser de {$alerts['radius_min']} a {$alerts['radius_max']} km.",
            'radius_km.between' => "El radio debe ser de {$alerts['radius_min']} a {$alerts['radius_max']} km.",
        ];
    }

    /** @return array{label: string, latitude: float, longitude: float, radius_km: int} */
    public function zoneData(): array
    {
        return [
            'label' => trim($this->input('label')),
            'latitude' => (float) $this->input('latitude'),
            'longitude' => (float) $this->input('longitude'),
            'radius_km' => (int) $this->input('radius_km'),
        ];
    }
}
