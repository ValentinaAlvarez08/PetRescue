<?php

namespace App\Models;

use App\Models\Concerns\DescribesPet;
use App\Models\Concerns\Flaggable;
use App\Support\GeoDistance;
use App\Support\ReportMatcher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PetReport extends Model
{
    use DescribesPet, Flaggable, HasFactory;

    protected $fillable = [
        'management_token',
        'pet_profile_id',
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
        'reunited_at' => 'datetime',
        'hidden_at' => 'datetime',
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

        // HU-12: fecha del reencuentro para las historias de éxito.
        static::saving(function (PetReport $report) {
            if ($report->status !== 'reunido') {
                $report->reunited_at = null;
            } elseif (! $report->reunited_at) {
                $report->reunited_at = now();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        // La URL pública usa el id; el token privado solo se usa para gestionar.
        return 'id';
    }

    /** HU-10: perfil digital desde el que se creó el reporte de pérdida. */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(PetProfile::class, 'pet_profile_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->pet_name ?: ($this->type === 'perdida' ? 'Mascota perdida' : 'Mascota encontrada');
    }

    /**
     * HU-09 (CID 4): enlace que se comparte. Siempre es el público por id,
     * nunca el enlace privado de gestión con el token.
     */
    public function getPublicUrlAttribute(): string
    {
        return route('reports.show', $this);
    }

    /** HU-09 (CID 2): texto prellenado para las redes sociales. */
    public function getShareTextAttribute(): string
    {
        $place = $this->location_reference ? " cerca de {$this->location_reference}" : '';

        return $this->type === 'perdida'
            ? "🐾 ¡Ayúdanos a encontrar a {$this->display_name} ({$this->species_label})! Se perdió{$place}. Si la ves, mira el reporte en PetRescue:"
            : "🐾 Encontramos esta mascota ({$this->species_label}){$place}. ¿Sabes quién es su familia? Mira el reporte en PetRescue:";
    }

    /**
     * HU-09 (CID 1 y 2): enlaces para compartir en cada red social.
     *
     * @return array<string, string>
     */
    public function shareLinks(): array
    {
        $url = rawurlencode($this->public_url);
        $text = rawurlencode($this->share_text);

        return [
            'whatsapp' => "https://wa.me/?text={$text}%20{$url}",
            'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$url}",
            'x' => "https://x.com/intent/post?text={$text}&url={$url}",
        ];
    }

    /**
     * Reportes que siguen buscando. HU-20: los ocultos por denuncias tampoco
     * cuentan (no aparecen en el listado, el mapa, las alertas ni las coincidencias).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'activo')->visible();
    }

    /**
     * HU-05: reportes del tipo contrario que podrían ser esta misma mascota.
     *
     * @return Collection<int, self>
     */
    public function similarReports(?int $limit = null): Collection
    {
        return ReportMatcher::for($this, $limit);
    }

    /** HU-20 */
    public function flagLabel(): string
    {
        return "el reporte «{$this->display_name}»";
    }

    public function flagUrl(): string
    {
        return $this->public_url;
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Sprint 3 — HU-04: reportes que tienen $color entre sus colores.
     * La columna guarda llaves separadas por coma ("negro,blanco"), así que
     * se compara la llave completa para que "gris" no coincida con otra.
     */
    public function scopeWithColor(Builder $query, string $color): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->where('color', $color)
            ->orWhere('color', 'like', $color.',%')
            ->orWhere('color', 'like', '%,'.$color)
            ->orWhere('color', 'like', '%,'.$color.',%'));
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
