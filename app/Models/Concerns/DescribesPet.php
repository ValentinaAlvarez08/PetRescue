<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Datos de la mascota que comparten los reportes (PetReport) y el perfil
 * digital (PetProfile, Sprint 4 — HU-10): especie, colores, foto y WhatsApp.
 * Necesita las columnas species, color, photo_path y contact_phone.
 */
trait DescribesPet
{
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

    /** @return array<int, string> llaves de color (para marcar los chips del formulario). */
    public function getColorKeysAttribute(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->color))));
    }

    /**
     * URL pública de la foto. Las fotos subidas viven en el disco "public";
     * las de los datos de prueba (DatabaseSeeder) vienen incluidas en el
     * repositorio, en public/images/seed/.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        return Str::startsWith($this->photo_path, 'images/')
            ? asset($this->photo_path)
            : Storage::url($this->photo_path);
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

    /** Borra la foto subida (las de datos de ejemplo viven en public/images/). */
    public function deleteUploadedPhoto(): void
    {
        if ($this->photo_path && ! Str::startsWith($this->photo_path, 'images/')) {
            Storage::disk('public')->delete($this->photo_path);
        }
    }
}
