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
        'sex',
        'event_date',
        'pet_name',
        'description',
        'photo_path',
        'contact_phone',
        'contact_email',
        'contact_whatsapp',
        'latitude',
        'longitude',
        'location_reference',
        'status',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'event_date' => 'date',
        'contact_whatsapp' => 'boolean',
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

    public function getRouteKeyName(): string
    {
        // La URL pública usa el id; el token privado solo se usa para gestionar.
        return 'id';
    }

    /** Etiqueta legible de la especie (ej. "perro" -> "Perro"). */
    public function getSpeciesLabelAttribute(): string
    {
        return config("pets.species.{$this->species}.label", ucfirst((string) $this->species));
    }

    public function getSpeciesEmojiAttribute(): string
    {
        return config("pets.species.{$this->species}.emoji", '🐾');
    }

    /**
     * Los colores se guardan como llaves separadas por coma ("negro,blanco").
     * Los reportes del Sprint 1 guardaban texto libre: se muestran tal cual.
     */
    public function getColorLabelsAttribute(): string
    {
        $catalog = config('pets.colors');

        return collect(explode(',', (string) $this->color))
            ->map(fn ($key) => trim($key))
            ->filter()
            ->map(fn ($key) => $catalog[$key]['label'] ?? $key)
            ->implode(', ');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->pet_name ?: ($this->type === 'perdida' ? 'Mascota perdida' : 'Mascota encontrada');
    }

    /** Número en formato internacional para enlaces de WhatsApp (HU-06). */
    public function getWhatsappNumberAttribute(): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $this->contact_phone);
        if (! $digits) {
            return null;
        }

        return strlen($digits) === 10 ? '57'.$digits : $digits;
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
