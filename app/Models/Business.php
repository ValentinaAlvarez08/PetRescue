<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Sprint 2 — HU-16: negocio o servicio del directorio para mascotas.
 */
class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'category', 'description', 'services', 'phone', 'has_whatsapp',
        'email', 'address', 'schedule', 'latitude', 'longitude', 'is_active',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'has_whatsapp' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInCategory(Builder $query, ?string $category): Builder
    {
        return $category ? $query->where('category', $category) : $query;
    }

    public function getCategoryInfoAttribute(): array
    {
        return config("community.directory.{$this->category}", [
            'label' => ucfirst($this->category), 'short' => ucfirst($this->category),
            'emoji' => '📍', 'color' => '#6b7280', 'blurb' => '',
        ]);
    }

    public function getWhatsappNumberAttribute(): ?string
    {
        if (! $this->has_whatsapp || ! $this->phone) {
            return null;
        }
        $digits = preg_replace('/\D/', '', $this->phone);

        return strlen($digits) === 10 ? '57'.$digits : $digits;
    }

    /** Datos que necesita el mapa del directorio (JSON en la vista). */
    public function toMapArray(): array
    {
        $info = $this->category_info;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'category_label' => $info['short'],
            'emoji' => $info['emoji'],
            'color' => $info['color'],
            'description' => $this->description,
            'services' => $this->services ? array_map('trim', explode(',', $this->services)) : [],
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp_number,
            'email' => $this->email,
            'address' => $this->address,
            'schedule' => $this->schedule,
            'lat' => $this->latitude,
            'lng' => $this->longitude,
        ];
    }
}
