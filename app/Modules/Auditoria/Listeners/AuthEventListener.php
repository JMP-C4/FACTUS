<?php

declare(strict_types=1);

namespace App\Modules\Auditoria\Listeners;

use App\Modules\Auditoria\Contracts\AuditLoggerContract;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class AuthEventListener
{
    public function __construct(
        private readonly AuditLoggerContract $auditLogger,
    ) {}

    public function login(Login $event): void
    {
        $this->auditLogger->log(
            event: 'login',
            module: 'auth',
            description: "Inicio de sesión: {$event->user->email}",
            subject: $event->user,
            userId: (int) $event->user->getAuthIdentifier(),
        );
    }

    public function logout(Logout $event): void
    {
        if ($event->user === null) {
            return;
        }

        $this->auditLogger->log(
            event: 'logout',
            module: 'auth',
            description: "Cierre de sesión: {$event->user->email}",
            subject: $event->user,
            userId: (int) $event->user->getAuthIdentifier(),
        );
    }

    public function failed(Failed $event): void
    {
        $email = (string) ($event->credentials['email'] ?? 'desconocido');

        $this->auditLogger->log(
            event: 'login_failed',
            module: 'auth',
            description: "Intento de acceso fallido: {$email}",
        );
    }
}
