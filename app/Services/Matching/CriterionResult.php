<?php

namespace App\Services\Matching;

/**
 * Resultado de un criterio de coincidencia: qué fracción de su peso suma
 * (0 a 1) y una razón legible para mostrar ("Mismo color: negro").
 * Un resultado "rechazado" descarta la pareja (por ejemplo, fechas imposibles).
 */
final class CriterionResult
{
    private function __construct(
        public readonly float $fraction,
        public readonly ?string $reason,
        public readonly bool $rejected,
    ) {
    }

    public static function full(?string $reason = null): self
    {
        return new self(1.0, $reason, false);
    }

    public static function partial(float $fraction, ?string $reason = null): self
    {
        return new self(max(0.0, min(1.0, $fraction)), $reason, false);
    }

    /** Dato que alguno de los dos no sabe: no confirma, pero tampoco descarta. */
    public static function unknown(): self
    {
        return new self(0.5, null, false);
    }

    public static function none(): self
    {
        return new self(0.0, null, false);
    }

    public static function reject(): self
    {
        return new self(0.0, null, true);
    }
}
