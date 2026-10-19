<?php

namespace App\Modules\Automatizacion\Automation;

/**
 * Resultado de la ejecución de un proceso automatizado.
 *
 * Contiene un estado, un mensaje legible y métricas clave/valor que se
 * muestran al usuario tras finalizar la tarea.
 */
final class AutomationResult
{
    /**
     * @param  array<string, mixed>  $metrics
     */
    public function __construct(
        public readonly bool $ok,
        public readonly string $message,
        public readonly array $metrics = [],
    ) {}

    /**
     * @param  array<string, mixed>  $metrics
     */
    public static function success(string $message, array $metrics = []): self
    {
        return new self(true, $message, $metrics);
    }

    /**
     * @param  array<string, mixed>  $metrics
     */
    public static function failure(string $message, array $metrics = []): self
    {
        return new self(false, $message, $metrics);
    }
}
