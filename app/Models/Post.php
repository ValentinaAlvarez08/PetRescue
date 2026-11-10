<?php

namespace App\Models;

use App\Contracts\Flaggable as FlaggableContract;
use App\Contracts\PhotoStorage;
use App\Models\Concerns\Flaggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Sprint 3 — HU-17: publicación en un espacio de la comunidad.
 */
class Post extends Model implements FlaggableContract
{
    use Flaggable, HasFactory;

    protected $fillable = [
        'management_token', 'topic', 'nickname', 'title', 'body', 'photo_path', 'helpful_count',
    ];

    protected $casts = [
        'helpful_count' => 'integer',
        'hidden_at' => 'datetime',
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

    /**
     * HU-18: comentarios, del más antiguo al más reciente (orden de conversación).
     * HU-20: sin los ocultos por denuncias.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('comments.hidden_at')->oldest();
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
        return app(PhotoStorage::class)->url($this->photo_path);
    }

    /** Borra la foto subida (las de datos de ejemplo viven en public/images/). */
    public function deleteUploadedPhoto(): void
    {
        app(PhotoStorage::class)->delete($this->photo_path);
    }

    /** HU-20 */
    public function flagLabel(): string
    {
        return "la publicación «{$this->title}»";
    }

    public function flagUrl(): string
    {
        return route('posts.show', [$this->topic, $this]);
    }

    /** Nombre de la cookie que evita sumar "Me sirvió" dos veces (HU-18). */
    public function helpfulCookieName(): string
    {
        return 'helpful_post_'.$this->id;
    }
}
