<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Sprint 3 — HU-04: filtros del listado de reportes.
 *
 * Todos viajan en la URL (query string) para poder compartir la búsqueda;
 * un valor que no exista en config/pets.php simplemente se ignora y el
 * radio se limita a 1–20 km.
 */
final class ReportFilters
{
    /** Radios que se ofrecen en el filtro (km). */
    public const RADIUS_OPTIONS = [1, 2, 5, 10, 20];

    private const DEFAULT_RADIUS = 5.0;

    private const MIN_RADIUS = 1.0;

    private const MAX_RADIUS = 20.0;

    private function __construct(
        public readonly ?string $type,
        public readonly ?string $species,
        public readonly ?string $color,
        public readonly float $radius,
        public readonly ?float $lat,
        public readonly ?float $lng,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $pick = fn (string $key, array $allowed) => in_array($request->query($key), $allowed, true) ? $request->query($key) : null;
        $number = fn (string $key) => is_numeric($request->query($key)) ? (float) $request->query($key) : null;

        return new self(
            type: $pick('type', ['perdida', 'encontrada']),
            species: $pick('species', array_keys(config('pets.species'))),
            color: $pick('color', array_keys(config('pets.colors'))),
            radius: min(self::MAX_RADIUS, max(self::MIN_RADIUS, $number('radius') ?? self::DEFAULT_RADIUS)),
            lat: $number('lat'),
            lng: $number('lng'),
        );
    }

    /** Aplica tipo, especie y color a la consulta (la cercanía la resuelve NearbyFinder). */
    public function apply(Builder $query): Builder
    {
        return $query
            ->when($this->type, fn (Builder $q) => $q->ofType($this->type))
            ->when($this->species, fn (Builder $q) => $q->where('species', $this->species))
            ->when($this->color, fn (Builder $q) => $q->withColor($this->color));
    }

    public function hasLocation(): bool
    {
        return $this->lat !== null && $this->lng !== null;
    }

    public function isFiltering(): bool
    {
        return $this->type || $this->species || $this->color || $this->lat !== null;
    }

    /** @return array{type: ?string, species: ?string, color: ?string, radius: float} */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'species' => $this->species,
            'color' => $this->color,
            'radius' => $this->radius,
        ];
    }
}
