<?php

namespace App\Models;

use App\Support\GeoDistance;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Subscriber extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'latitude',
        'longitude',
        'radius_km',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'radius_km' => 'decimal:2',
        'notifications_enabled' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Subscriber $subscriber) {
            if (empty($subscriber->unsubscribe_token)) {
                $subscriber->unsubscribe_token = (string) Str::uuid();
            }
        });
    }

    /**
     * HU3 (CID 1): suscriptores con notificaciones activas cuyo radio
     * configurado cubre la coordenada donde se publicó un reporte.
     *
     * @return Collection<int, self>
     */
    public static function near(float $lat, float $lng): Collection
    {
        return static::query()
            ->where('notifications_enabled', true)
            ->get()
            ->filter(function (self $subscriber) use ($lat, $lng) {
                $subscriber->distance_km = GeoDistance::kilometers(
                    $lat,
                    $lng,
                    (float) $subscriber->latitude,
                    (float) $subscriber->longitude
                );

                return $subscriber->distance_km <= (float) $subscriber->radius_km;
            })
            ->values();
    }
}
