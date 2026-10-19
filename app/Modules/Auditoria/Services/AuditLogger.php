<?php

namespace App\Modules\Auditoria\Services;

use App\Modules\Auditoria\Contracts\AuditLoggerContract;
use App\Modules\Auditoria\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger implements AuditLoggerContract
{
    /**
     * Claves que nunca deben quedar registradas en la bitácora.
     *
     * @var array<int, string>
     */
    private const REDACTED_KEYS = [
        'password',
        'password_confirmation',
        'secret',
        'token',
        'access_token',
        'refresh_token',
    ];

    public function log(
        string $event,
        string $module,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        ?int $userId = null,
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $userId ?? Auth::id(),
            'event' => $event,
            'module' => $module,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $this->sanitize($properties),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Elimina de las propiedades cualquier dato sensible.
     *
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>
     */
    private function sanitize(array $properties): array
    {
        foreach ($properties as $key => $value) {
            if (is_array($value)) {
                $properties[$key] = $this->sanitize($value);

                continue;
            }

            if (in_array(strtolower((string) $key), self::REDACTED_KEYS, true)) {
                $properties[$key] = '[redacted]';
            }
        }

        return $properties;
    }
}
