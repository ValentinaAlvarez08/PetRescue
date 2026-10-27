<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * HU3 / HU-11: persona (sin cuenta) que recibe avisos por correo. Se
 * identifica por su correo y puede vigilar varias zonas (AlertZone).
 */
class Subscriber extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
    ];

    protected static function booted(): void
    {
        // HU-11: enlace privado para administrar todas las zonas, sin login
        // (mismo patrón del management_token de pet_reports).
        static::creating(function (Subscriber $subscriber) {
            if (empty($subscriber->manage_token)) {
                $subscriber->manage_token = (string) Str::uuid();
            }
        });
    }

    /** Un correo = un suscriptor, sin importar mayúsculas o espacios. */
    public function setEmailAttribute(string $value): void
    {
        $this->attributes['email'] = Str::lower(trim($value));
    }

    public function zones(): HasMany
    {
        return $this->hasMany(AlertZone::class)->orderBy('id');
    }
}
