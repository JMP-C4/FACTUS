<?php

namespace App\Modules\Automatizacion\Services;

use App\Models\User;
use App\Modules\Auditoria\Contracts\AuditLoggerContract;
use App\Modules\Automatizacion\Automation\AutomationProcess;
use App\Modules\Automatizacion\Automation\AutomationResult;
use Throwable;

/**
 * Ejecuta procesos automatizados y registra el resultado en la bitácora.
 */
class AutomationRunner
{
    public function __construct(
        private readonly AuditLoggerContract $auditLogger,
    ) {}

    /**
     * Ejecuta un proceso capturando cualquier error para no romper la petición.
     */
    public function run(AutomationProcess $process, User $user): AutomationResult
    {
        try {
            $result = $process->handle($user->company_id ?? 0, $user->id);
        } catch (Throwable $exception) {
            report($exception);

            $result = AutomationResult::failure('La ejecución falló: '.$exception->getMessage());
        }

        $this->auditLogger->log(
            event: 'automation.run',
            module: 'automatizacion',
            description: "Proceso '{$process->label()}' ejecutado.",
            subject: $user,
            properties: [
                'process' => $process->key(),
                'success' => $result->ok,
                'metrics' => $result->metrics,
            ],
        );

        return $result;
    }
}
