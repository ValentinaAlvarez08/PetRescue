<?php

namespace App\Models\Concerns;

use App\Models\ContentFlag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Sprint 4 — HU-20: contenido que la comunidad puede denunciar
 * (reportes, publicaciones y comentarios). Necesita la columna hidden_at.
 */
trait Flaggable
{
    public function flags(): MorphMany
    {
        return $this->morphMany(ContentFlag::class, 'flaggable');
    }

    /** Contenido que no fue ocultado por denuncias. */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereNull($this->qualifyColumn('hidden_at'));
    }

    public function isHidden(): bool
    {
        return $this->hidden_at !== null;
    }

    /**
     * Oculta el contenido al llegar al mínimo de denuncias. Devuelve true
     * solo la vez que lo oculta (para avisar a moderación una sola vez).
     */
    public function hideIfFlaggedEnough(): bool
    {
        if ($this->isHidden() || $this->flags()->count() < config('community.flags.hide_threshold')) {
            return false;
        }

        $this->forceFill(['hidden_at' => now()])->save();

        return true;
    }

    /** Vuelve a mostrar el contenido y borra sus denuncias (revisión de moderación). */
    public function restoreFromFlags(): void
    {
        $this->flags()->delete();
        $this->forceFill(['hidden_at' => null])->save();
    }

    /** Nombre corto para la persona que modera ("el reporte", "la publicación"...). */
    abstract public function flagLabel(): string;

    /** Enlace público para revisar el contenido. */
    abstract public function flagUrl(): string;
}
