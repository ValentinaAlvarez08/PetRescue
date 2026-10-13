<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sprint 3 — HU-18: comentario de una publicación de la comunidad.
 */
class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['post_id', 'nickname', 'body'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
