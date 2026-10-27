<?php

namespace App\Models;

use App\Models\Concerns\DescribesPet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * Sprint 4 — HU-10: perfil digital de la mascota. Igual que los reportes,
 * no hay login: el dueño lo administra con el enlace privado (token) y
 * comparte el enlace público (o el QR de la placa) con el código corto.
 */
class PetProfile extends Model
{
    use DescribesPet;

    protected $fillable = [
        'name', 'species', 'breed', 'sex', 'size', 'color', 'birth_date', 'photo_path',
        'distinctive_marks', 'sterilized', 'vaccines', 'health_notes',
        'owner_name', 'contact_phone', 'contact_whatsapp', 'contact_email', 'neighborhood',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'contact_whatsapp' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (PetProfile $profile) {
            if (empty($profile->management_token)) {
                $profile->management_token = (string) Str::uuid();
            }
            if (empty($profile->public_code)) {
                $profile->public_code = static::newPublicCode();
            }
        });
    }

    /** Código corto y difícil de adivinar para el enlace público y el QR. */
    public static function newPublicCode(): string
    {
        do {
            $code = Str::lower(Str::random(8));
        } while (static::where('public_code', $code)->exists());

        return $code;
    }

    public function getRouteKeyName(): string
    {
        return 'public_code';
    }

    public function reports(): HasMany
    {
        return $this->hasMany(PetReport::class)->latest();
    }

    /** Reporte de pérdida que sigue activo (para el aviso "¡Estoy perdida!"). */
    public function activeLostReport(): HasOne
    {
        return $this->hasOne(PetReport::class)->ofMany(
            ['id' => 'max'],
            fn ($query) => $query->where('type', 'perdida')->where('status', 'activo')->whereNull('hidden_at'),
        );
    }

    public function getPublicUrlAttribute(): string
    {
        return route('profiles.show', $this);
    }

    /** Edad aproximada a partir de la fecha de nacimiento ("2 años", "5 meses"). */
    public function getAgeLabelAttribute(): ?string
    {
        if (! $this->birth_date) {
            return null;
        }

        $months = (int) $this->birth_date->diffInMonths(now());

        return match (true) {
            $months >= 12 => ($years = intdiv($months, 12)).($years === 1 ? ' año' : ' años'),
            $months >= 1 => $months.($months === 1 ? ' mes' : ' meses'),
            default => 'Menos de un mes',
        };
    }

    /**
     * Datos para prellenar el reporte de pérdida (HU-10, CID 4).
     *
     * @return array<string, mixed>
     */
    public function toReportPrefill(): array
    {
        return [
            'species' => $this->species,
            'breed' => $this->breed,
            'pet_name' => $this->name,
            'sex' => $this->sex,
            'size' => $this->size,
            'colors' => $this->color_keys,
            'description' => $this->distinctive_marks,
            'contact_phone' => $this->contact_phone,
            'contact_email' => $this->contact_email,
            'contact_whatsapp' => $this->contact_whatsapp,
        ];
    }
}
