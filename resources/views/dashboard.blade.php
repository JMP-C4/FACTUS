<x-app-layout>
    <x-slot name="header">
        @include('dashboard.partials.header', [
            'isSuperAdmin' => auth()->user()->isSuperAdmin(),
        ])
    </x-slot>

    @php
        // Datos temporales de presentación para visualizar el panel.
        // Luego estos bloques saldrán desde servicios y consultas reales.
        $summary = [
            [
                'label' => 'Usuarios activos',
                'value' => '18',
                'caption' => 'Incluye operadores, supervisores y administradores distribuidos en compañías activas.',
                'tone' => 'blue',
            ],
            [
                'label' => 'Roles asignados',
                'value' => '42',
                'caption' => 'Asignaciones globales y contextuales preparadas para reflejar scope por empresa y sucursal.',
                'tone' => 'amber',
            ],
            [
                'label' => 'Compañías',
                'value' => '03',
                'caption' => 'Empresas listas para administrar catálogos, sucursales, inventario y facturación electrónica.',
                'tone' => 'black',
            ],
        ];

        $modules = [
            [
                'id' => 'usuarios',
                'span' => 'lg:col-span-2',
                'title' => 'Usuarios',
                'description' => 'Gobierna el acceso a la plataforma, altas administrativas y estados de operadores.',
                'metric' => '12 pendientes',
                'status' => 'revisar',
                'tone' => 'blue',
            ],
            [
                'id' => 'roles',
                'title' => 'Roles y permisos',
                'description' => 'Centraliza el RBAC, scopes globales, permisos por módulo y asignaciones de seguridad.',
                'metric' => '34 permisos',
                'status' => 'estable',
                'tone' => 'green',
            ],
            [
                'id' => 'companias',
                'title' => 'Compañías',
                'description' => 'Orquesta la separación multiempresa y deja lista la estructura para admins por contexto.',
                'metric' => '03 onboarded',
                'status' => 'creciendo',
                'tone' => 'amber',
            ],
            [
                'id' => 'facturacion',
                'title' => 'Facturación local',
                'description' => 'Emite facturas sin servicios externos: numeración, CUFE y QR generados de forma local.',
                'metric' => 'modo local',
                'status' => 'listo',
                'tone' => 'green',
            ],
            [
                'id' => 'logs',
                'title' => 'Logs del sistema',
                'description' => 'Concentra eventos de auditoría, errores críticos e intentos de acceso no autorizado.',
                'metric' => '24h stream',
                'status' => 'listo',
                'tone' => 'blue',
            ],
        ];

        $logRows = [
            ['event' => 'Login privilegiado', 'owner' => 'super_admin', 'context' => 'Global', 'status' => 'Success'],
            ['event' => 'Seed RBAC ejecutado', 'owner' => 'CLI bootstrap', 'context' => 'AccessControl', 'status' => 'Success'],
            ['event' => 'Facturación local activa', 'owner' => 'System', 'context' => 'Local', 'status' => 'Success'],
            ['event' => 'Scopes por compañía', 'owner' => 'Pending', 'context' => 'RBAC', 'status' => 'Next'],
        ];
    @endphp

    @if(auth()->user()->isSuperAdmin())
        @include('dashboard.partials.super-admin-shell', [
            'user' => auth()->user(),
            'summary' => $summary,
            'modules' => $modules,
            'logRows' => $logRows,
        ])
    @else
        @include('dashboard.partials.fallback-panel')
    @endif
</x-app-layout>
