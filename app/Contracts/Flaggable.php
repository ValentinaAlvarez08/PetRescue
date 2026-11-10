<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * HU-20: contenido que la comunidad puede denunciar.
 *
 * Los controladores, la moderación y los correos dependen de este contrato y
 * no de los modelos concretos: para hacer denunciable un modelo nuevo basta
 * con implementarlo (el trait App\Models\Concerns\Flaggable trae casi todo)
 * y agregarlo al morph map de AppServiceProvider.
 */
interface Flaggable
{
    public function flags(): MorphMany;

    public function isHidden(): bool;

    /** Oculta el contenido al llegar al mínimo de denuncias; true solo la vez que lo oculta. */
    public function hideIfFlaggedEnough(): bool;

    /** Vuelve a mostrar el contenido y borra sus denuncias. */
    public function restoreFromFlags(): void;

    /** Nombre corto para moderación ("el reporte «Luna»"). */
    public function flagLabel(): string;

    /** Enlace público para revisar el contenido. */
    public function flagUrl(): string;
}
