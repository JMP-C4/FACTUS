<?php

namespace App\Modules\Automatizacion\Processes;

use App\Modules\Auditoria\Models\ActivityLog;
use App\Modules\Automatizacion\Automation\AutomationProcess;
use App\Modules\Automatizacion\Automation\AutomationResult;

/**
 * Elimina los registros de auditoría más antiguos que la retención definida.
 */
class PurgeActivityLogsProcess extends AutomationProcess
{
    /**
     * Días de retención de la bitácora antes de depurarla.
     */
    private const RETENTION_DAYS = 90;

    public function key(): string
    {
        return 'plataforma.depurar_bitacora';
    }

    public function label(): string
    {
        return 'Depurar bitácora antigua';
    }

    public function description(): string
    {
        return 'Elimina los registros de auditoría con más de '.self::RETENTION_DAYS.' días de antigüedad. Aplica a toda la plataforma.';
    }

    public function permission(): string
    {
        return 'platform.logs.view';
    }

    public function icon(): string
    {
        return 'M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14M10 11h4';
    }

    public function dangerous(): bool
    {
        return true;
    }

    public function handle(int $companyId, int $userId): AutomationResult
    {
        $threshold = now()->subDays(self::RETENTION_DAYS);
        $deleted = ActivityLog::query()->where('created_at', '<', $threshold)->delete();

        $metrics = [
            'Retención (días)' => self::RETENTION_DAYS,
            'Eliminados' => $deleted,
        ];

        return AutomationResult::success(
            $deleted === 0
                ? 'No había registros antiguos que depurar.'
                : "Se depuraron {$deleted} registros de la bitácora.",
            $metrics,
        );
    }
}
