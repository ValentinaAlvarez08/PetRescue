<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** HU3: coordenadas y radio del radar de mascotas cercanas. */
class NearbyReportsRequest extends FormRequest
{
    private const DEFAULT_RADIUS_KM = 5;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric', 'min:0.5', 'max:100'],
        ];
    }

    public function latitude(): float
    {
        return (float) $this->query('lat');
    }

    public function longitude(): float
    {
        return (float) $this->query('lng');
    }

    public function radiusKm(): float
    {
        return (float) $this->query('radius', self::DEFAULT_RADIUS_KM);
    }
}
