<?php

namespace App\Models;

use App\Support\GeoDistance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Sprint 4 — HU-11: zona vigilada por un suscriptor (casa, trabajo, finca...).
 */
class AlertZone extends Model
{
    protected $fillable = [
        'label',
        'latitude',
        'longitude',
        'radius_km',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'radius_km' => 'decimal:2',
        'active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (AlertZone $zone) {
            if (empty($zone->unsubscribe_token)) {
                $zone->unsubscribe_token = (string) Str::uuid();
            }
        });
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }

    /** Radio sin decimales sobrantes ("5" en vez de "5.00"). */
    public function getRadiusLabelAttribute(): string
    {
        return rtrim(rtrim(number_format((float) $this->radius_km, 2, '.', ''), '0'), '.');
    }

    /**
     * HU3 / HU-11 (CID 2): zonas activas cuyo radio cubre la coordenada
     * donde se publicó un reporte. Cada zona trae su distance_km.
     *
     * @return Collection<int, self>
     */
    public static function covering(float $lat, float $lng): Collection
    {
        return static::query()
            ->with('subscriber')
            ->where('active', true)
            ->get()
            ->filter(function (self $zone) use ($lat, $lng) {
                $zone->distance_km = GeoDistance::kilometers(
                    $lat,
                    $lng,
                    (float) $zone->latitude,
                    (float) $zone->longitude
                );

                return $zone->distance_km <= (float) $zone->radius_km;
            })
            ->values();
    }
}
