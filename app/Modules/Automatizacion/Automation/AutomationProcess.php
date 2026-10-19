<?php

namespace App\Modules\Automatizacion\Automation;

/**
 * Contrato base para los procesos automatizados del sistema.
 *
 * Un proceso es una tarea que se ejecuta bajo demanda (al presionar un botón),
 * agrupa la lógica de un mantenimiento o rutina operativa y devuelve un
 * resultado con métricas para mostrar al usuario.
 */
abstract class AutomationProcess
{
    /**
     * Clave única del proceso (usada en la ruta y en la bitácora).
     */
    abstract public function key(): string;

    /**
     * Nombre legible mostrado en la interfaz.
     */
    abstract public function label(): string;

    /**
     * Descripción corta de lo que hace el proceso.
     */
    abstract public function description(): string;

    /**
     * Permiso RBAC requerido para ejecutar el proceso.
     */
    abstract public function permission(): string;

    /**
     * Ejecuta el proceso y devuelve su resultado.
     */
    abstract public function handle(int $companyId, int $userId): AutomationResult;

    /**
     * Ícono SVG (path) mostrado en la tarjeta del proceso.
     */
    public function icon(): string
    {
        return 'M13 2 3 14h7l-1 8 10-12h-7l1-8z';
    }

    /**
     * Indica si el proceso modifica datos de forma sensible.
     * Los procesos marcados como peligrosos requieren confirmación explícita.
     */
    public function dangerous(): bool
    {
        return false;
    }
}
