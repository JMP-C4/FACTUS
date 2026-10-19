<?php

namespace App\Modules\Auditoria\Providers;

use App\Modules\Auditoria\Contracts\AuditLoggerContract;
use App\Modules\Auditoria\Listeners\AuthEventListener;
use App\Modules\Auditoria\Services\AuditLogger;
use App\Modules\Support\Providers\BaseModuleServiceProvider;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;

class AuditoriaServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'Auditoria';

    protected function bindings(): array
    {
        return [
            AuditLoggerContract::class => AuditLogger::class,
        ];
    }

    protected function singletons(): array
    {
        return [
            AuditLogger::class => AuditLogger::class,
        ];
    }

    public function boot(): void
    {
        parent::boot();

        // Instrumenta los eventos de autenticación en la bitácora.
        Event::listen(Login::class, [AuthEventListener::class, 'login']);
        Event::listen(Logout::class, [AuthEventListener::class, 'logout']);
        Event::listen(Failed::class, [AuthEventListener::class, 'failed']);
    }
}
