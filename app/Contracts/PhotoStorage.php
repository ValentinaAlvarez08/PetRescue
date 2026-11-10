<?php

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

/**
 * Fotos de reportes, perfiles y publicaciones. Las rutas que empiezan por
 * "images/" son de los datos de ejemplo (public/images) y nunca se borran.
 */
interface PhotoStorage
{
    /** Guarda la foto en la carpeta y devuelve su ruta. */
    public function store(UploadedFile $file, string $folder): string;

    /** Copia una foto existente a otra carpeta y devuelve la ruta de la copia. */
    public function copy(string $path, string $folder): string;

    /** Borra una foto subida (las de ejemplo se ignoran). */
    public function delete(?string $path): void;

    /** URL pública de la foto, o null si no hay. */
    public function url(?string $path): ?string;
}
