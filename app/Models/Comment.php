<?php

namespace App\Models;

use App\Models\Concerns\Flaggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sprint 3 — HU-18: comentario de una publicación de la comunidad.
 */
class Comment extends Model
{
    use Flaggable, HasFactory;

    protected $fillable = ['post_id', 'nickname', 'body'];

    protected $casts = [
        'hidden_at' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /** HU-20 */
    public function flagLabel(): string
    {
        return "el comentario de {$this->nickname}";
    }

    public function flagUrl(): string
    {
        return route('posts.show', [$this->post->topic, $this->post_id]).'#comentario-'.$this->id;
    }
}
