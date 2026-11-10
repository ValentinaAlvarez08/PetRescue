<?php

namespace App\Services\Photos;

use App\Contracts\PhotoStorage;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Guarda las fotos en un disco de Laravel: "public" en local o "supabase"
 * (Supabase Storage) en producción, según config('filesystems.photos_disk').
 * Las de los datos de ejemplo viven en public/images/ y no se tocan.
 */
class PublicDiskPhotoStorage implements PhotoStorage
{
    private const SAMPLE_PREFIX = 'images/';

    public function __construct(private readonly string $diskName = 'public')
    {
    }

    public function store(UploadedFile $file, string $folder): string
    {
        return $file->store($folder, $this->diskName);
    }

    public function copy(string $path, string $folder): string
    {
        if ($this->isSample($path)) {
            return $path;
        }

        $copy = trim($folder, '/').'/'.Str::uuid().'.'.pathinfo($path, PATHINFO_EXTENSION);
        $this->disk()->copy($path, $copy);

        return $copy;
    }

    public function delete(?string $path): void
    {
        if ($path && ! $this->isSample($path)) {
            $this->disk()->delete($path);
        }
    }

    public function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return $this->isSample($path) ? asset($path) : $this->disk()->url($path);
    }

    private function isSample(string $path): bool
    {
        return Str::startsWith($path, self::SAMPLE_PREFIX);
    }

    private function disk(): Filesystem
    {
        // Se resuelve en cada uso para respetar Storage::fake() en las pruebas.
        return Storage::disk($this->diskName);
    }
}
