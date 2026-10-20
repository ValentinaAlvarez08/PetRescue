<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Sprint 3 — HU-17: publicación en un espacio de la comunidad.
 */
class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'management_token', 'topic', 'nickname', 'title', 'body', 'photo_path', 'helpful_count',
    ];

    protected $casts = [
        'helpful_count' => 'integer',
    ];

    protected static function booted(): void
    {
        // Igual que en los reportes: un token privado reemplaza el login.
        static::creating(function (Post $post) {
            if (empty($post->management_token)) {
                $post->management_token = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        // La URL pública usa el id; el token solo se usa para gestionar.
        return 'id';
    }

    /** HU-18: comentarios, del más antiguo al más reciente (orden de conversación). */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->oldest();
    }

    public function scopeInTopic(Builder $query, string $topic): Builder
    {
        return $query->where('topic', $topic);
    }

    /** Datos del tema (etiqueta, emoji) desde config/community.php. */
    public function getTopicDataAttribute(): array
    {
        return config("community.topics.{$this->topic}", ['label' => $this->topic, 'emoji' => '💬', 'blurb' => '']);
    }

    /** Fotos subidas en el disco "public"; las de datos de ejemplo en public/images/. */
    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        return Str::startsWith($this->photo_path, 'images/')
            ? asset($this->photo_path)
            : Storage::url($this->photo_path);
    }

    /** Nombre de la cookie que evita sumar "Me sirvió" dos veces (HU-18). */
    public function helpfulCookieName(): string
    {
        return 'helpful_post_'.$this->id;
    }
}
