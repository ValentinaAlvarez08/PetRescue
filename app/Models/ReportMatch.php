<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sprint 4 — HU-08: posible coincidencia ya avisada entre una mascota
 * perdida y una encontrada.
 */
class ReportMatch extends Model
{
    protected $fillable = ['lost_report_id', 'found_report_id', 'score', 'notified_at'];

    protected $casts = [
        'score' => 'integer',
        'notified_at' => 'datetime',
    ];

    public function lostReport(): BelongsTo
    {
        return $this->belongsTo(PetReport::class, 'lost_report_id');
    }

    public function foundReport(): BelongsTo
    {
        return $this->belongsTo(PetReport::class, 'found_report_id');
    }
}
