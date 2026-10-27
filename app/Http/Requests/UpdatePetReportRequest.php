<?php

namespace App\Http\Requests;

use App\Models\PetReport;
use Illuminate\Support\Carbon;

/**
 * Sprint 4 — HU-07: edición del reporte con el enlace privado. Usa las mismas
 * reglas que al publicar, con tres diferencias:
 * - el tipo (perdida/encontrada) y la especie no cambian;
 * - la foto es opcional (si no se sube otra, se deja la actual);
 * - se acepta la fecha original aunque ya tenga más de 90 días.
 */
class UpdatePetReportRequest extends StorePetReportRequest
{
    private ?PetReport $report = null;

    public function managedReport(): PetReport
    {
        return $this->report ??= PetReport::where('management_token', $this->route('token'))->firstOrFail();
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $this->merge([
            'type' => $this->managedReport()->type,
            'species' => $this->managedReport()->species,
        ]);
    }

    public function rules(): array
    {
        $rules = parent::rules();

        $rules['photo'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];
        unset($rules['pet_profile_token']);

        $original = $this->managedReport()->event_date;
        $minDate = now()->subDays(config('pets.max_days_ago'))->startOfDay();
        if ($original && $original->lt($minDate)) {
            $minDate = Carbon::parse($original);
        }
        $rules['event_date'] = ['required', 'date', 'before_or_equal:today', 'after_or_equal:'.$minDate->toDateString()];

        return $rules;
    }

    public function reportData(): array
    {
        $data = parent::reportData();
        unset($data['type'], $data['species'], $data['pet_profile_id']);

        return $data;
    }
}
