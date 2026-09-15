<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PetReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'management_token',
        'type',
        'pet_name',
        'description',
        'photo_path',
        'contact_phone',
        'latitude',
        'longitude',
        'location_reference',
        'status',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    protected static function booted(): void
    {
        // HU1 / HU2: se genera un token privado en vez de pedir login.
        static::creating(function (PetReport $report) {
            if (empty($report->management_token)) {
                $report->management_token = (string) Str::uuid();
            }
            if (empty($report->status)) {
                $report->status = 'activo';
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'activo');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * HU3: reportes activos cerca de una coordenada, usando la fórmula
     * de Haversine directamente en la consulta (MySQL).
     */
    public function scopeNear(Builder $query, float $lat, float $lng, float $radiusKm = 5): Builder
    {
        $haversine = "(6371 * acos(cos(radians($lat))
            * cos(radians(latitude))
            * cos(radians(longitude) - radians($lng))
            + sin(radians($lat))
            * sin(radians(latitude))))";

        return $query
            ->selectRaw("pet_reports.*, {$haversine} AS distance_km")
            ->having('distance_km', '<=', $radiusKm)
            ->orderBy('distance_km');
    }
}
