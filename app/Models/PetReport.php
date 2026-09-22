<?php

namespace App\Models;

use App\Support\GeoDistance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PetReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'management_token',
        'type',
        'species',
        'breed',
        'color',
        'size',
        'pet_name',
        'description',
        'photo_path',
        'contact_phone',
        'contact_email',
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
     * HU3: reportes de $query que caen dentro de $radiusKm de una coordenada,
     * ordenados por cercanía. El cálculo se hace en PHP (no en SQL) para que
     * funcione igual en SQLite (desarrollo) y en MySQL (producción).
     *
     * @return Collection<int, self>
     */
    public static function near(Builder $query, float $lat, float $lng, float $radiusKm = 5): Collection
    {
        return $query->get()
            ->filter(function (self $report) use ($lat, $lng, $radiusKm) {
                $report->distance_km = GeoDistance::kilometers(
                    $lat,
                    $lng,
                    (float) $report->latitude,
                    (float) $report->longitude
                );

                return $report->distance_km <= $radiusKm;
            })
            ->sortBy('distance_km')
            ->values();
    }
}
