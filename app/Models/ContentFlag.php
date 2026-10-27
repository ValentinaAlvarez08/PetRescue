<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Sprint 4 — HU-20: denuncia de un reporte, publicación o comentario.
 */
class ContentFlag extends Model
{
    protected $fillable = ['reason', 'details', 'reporter_hash'];

    public function flaggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getReasonLabelAttribute(): string
    {
        return config("community.flags.reasons.{$this->reason}", $this->reason);
    }
}
