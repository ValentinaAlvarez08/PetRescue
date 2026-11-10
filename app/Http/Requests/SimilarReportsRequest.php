<?php

namespace App\Http\Requests;

use App\Models\PetReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Sprint 4 — HU-05: datos del formulario (todavía sin publicar) con los que
 * se buscan reportes parecidos.
 */
class SimilarReportsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $bounds = config('pets.bounds');

        return [
            'type' => ['required', Rule::in(['perdida', 'encontrada'])],
            'species' => ['required', Rule::in(array_keys(config('pets.species')))],
            'latitude' => ['required', 'numeric', 'between:'.$bounds['lat_min'].','.$bounds['lat_max']],
            'longitude' => ['required', 'numeric', 'between:'.$bounds['lng_min'].','.$bounds['lng_max']],
            'breed' => ['nullable', 'string', 'max:100'],
            'sex' => ['nullable', 'string', 'max:10'],
            'size' => ['nullable', 'string', 'max:20'],
            'colors' => ['nullable', 'array', 'max:'.config('pets.max_colors')],
            'colors.*' => ['string', 'max:30'],
            'event_date' => ['nullable', 'date'],
        ];
    }

    /** Reporte sin guardar con los datos del formulario. */
    public function draftReport(): PetReport
    {
        $data = $this->validated();

        $draft = new PetReport(collect($data)->except('colors')->all());
        $draft->color = implode(',', $data['colors'] ?? []);

        if ($draft->breed === config('pets.unknown_breed')) {
            $draft->breed = null;
        }

        return $draft;
    }
}
