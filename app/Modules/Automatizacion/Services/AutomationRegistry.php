<?php

namespace App\Modules\Automatizacion\Services;

use App\Models\User;
use App\Modules\Automatizacion\Automation\AutomationProcess;
use App\Modules\Automatizacion\Processes\IssueDraftInvoicesProcess;
use App\Modules\Automatizacion\Processes\LowStockAlertProcess;
use App\Modules\Automatizacion\Processes\PurgeActivityLogsProcess;
use App\Modules\Automatizacion\Processes\RebuildStockProcess;

/**
 * Catálogo de procesos automatizados disponibles en la plataforma.
 */
class AutomationRegistry
{
    /**
     * Procesos registrados en la plataforma.
     *
     * @var array<int, class-string<AutomationProcess>>
     */
    private array $processes = [
        IssueDraftInvoicesProcess::class,
        RebuildStockProcess::class,
        LowStockAlertProcess::class,
        PurgeActivityLogsProcess::class,
    ];

    /**
     * Todos los procesos, indexados por su clave.
     *
     * @return array<string, AutomationProcess>
     */
    public function all(): array
    {
        $resolved = [];

        foreach ($this->processes as $process) {
            $instance = app($process);
            $resolved[$instance->key()] = $instance;
        }

        return $resolved;
    }

    /**
     * Busca un proceso por su clave.
     */
    public function find(string $key): ?AutomationProcess
    {
        return $this->all()[$key] ?? null;
    }

    /**
     * Procesos que un usuario puede ejecutar según sus permisos.
     *
     * @return array<string, AutomationProcess>
     */
    public function runnableFor(User $user): array
    {
        return array_filter(
            $this->all(),
            static fn (AutomationProcess $process): bool => $user->hasPermission($process->permission()),
        );
    }
}
