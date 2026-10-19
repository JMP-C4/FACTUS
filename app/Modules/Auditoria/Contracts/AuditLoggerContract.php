<?php

namespace App\Modules\Auditoria\Contracts;

use App\Modules\Auditoria\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

interface AuditLoggerContract
{
    /**
     * Registra un evento de auditoría.
     *
     * @param  array<string, mixed>  $properties
     */
    public function log(
        string $event,
        string $module,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        ?int $userId = null,
    ): ActivityLog;
}
