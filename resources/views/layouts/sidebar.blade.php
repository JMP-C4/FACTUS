@php
    $user = auth()->user();

    $operationItems = [
        [
            'label' => 'Dashboard',
            'href' => route('dashboard'),
            'active' => request()->routeIs('dashboard'),
            'permission' => null,
            'icon' => 'M3 10.5 12 3l9 7.5M5 9.5V21h14V9.5',
        ],
        [
            'label' => 'POS',
            'href' => route('facturacion.pos'),
            'active' => request()->routeIs('facturacion.pos'),
            'permission' => 'pos.vender',
            'icon' => 'M3 4h2l2.4 12.6a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L21 8H6',
        ],
        [
            'label' => 'Facturas',
            'href' => route('facturacion.facturas.index'),
            'active' => request()->routeIs('facturacion.facturas.*'),
            'permission' => 'facturacion.view',
            'icon' => 'M7 3h7l5 5v13H7zM14 3v5h5M9 13h6M9 17h6',
        ],
        [
            'label' => 'Notas de crédito',
            'href' => route('facturacion.notas.index'),
            'active' => request()->routeIs('facturacion.notas.*'),
            'permission' => 'facturacion.view',
            'icon' => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M9 13l6 6M15 13l-6 6',
        ],
        [
            'label' => 'Productos',
            'href' => route('productos.index'),
            'active' => request()->routeIs('productos.*'),
            'permission' => 'productos.view',
            'icon' => 'M21 8 12 3 3 8v8l9 5 9-5zM3 8l9 5 9-5M12 13v8',
        ],
        [
            'label' => 'Clientes',
            'href' => route('clientes.index'),
            'active' => request()->routeIs('clientes.*'),
            'permission' => 'clientes.view',
            'icon' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M22 21v-2a4 4 0 0 0-3-3.87',
        ],
        [
            'label' => 'Inventario',
            'href' => route('inventario.movimientos.index'),
            'active' => request()->routeIs('inventario.*'),
            'permission' => 'inventario.view',
            'icon' => 'M3 7h18v13H3zM3 4h18v3H3zM10 11h4',
        ],
        [
            'label' => 'Reportes',
            'href' => route('reportes.ventas.index'),
            'active' => request()->routeIs('reportes.*'),
            'permission' => 'reportes.ventas',
            'icon' => 'M3 3v18h18M8 16v-5M13 16V8M18 16v-9',
        ],
    ];

    $adminItems = [
        [
            'label' => 'Compañías',
            'href' => route('empresas.index'),
            'active' => request()->routeIs('empresas.*'),
            'permission' => 'empresas.view',
            'icon' => 'M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01',
        ],
        [
            'label' => 'Sucursales',
            'href' => route('sucursales.index'),
            'active' => request()->routeIs('sucursales.*'),
            'permission' => 'sucursales.view',
            'icon' => 'M3 21h18M6 21V9l6-4 6 4v12M10 21v-5h4v5',
        ],
        [
            'label' => 'Impuestos',
            'href' => route('productos.impuestos.index'),
            'active' => request()->routeIs('productos.impuestos.*'),
            'permission' => 'impuestos.manage',
            'super_admin' => true,
            'icon' => 'M9 7h6M9 11h6M9 15h3M5 3h14v18l-7-3-7 3z',
        ],
        [
            'label' => 'Categorías',
            'href' => route('productos.categorias.index'),
            'active' => request()->routeIs('productos.categorias.*'),
            'permission' => 'categorias.manage',
            'icon' => 'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z',
        ],
        [
            'label' => 'Marcas',
            'href' => route('productos.marcas.index'),
            'active' => request()->routeIs('productos.marcas.*'),
            'permission' => 'marcas.manage',
            'icon' => 'M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0l-8-8V3h9.6l8.4 8.4a2 2 0 0 1 0 2.8zM7.5 7.5h.01',
        ],
        [
            'label' => 'Almacenes',
            'href' => route('inventario.almacenes.index'),
            'active' => request()->routeIs('inventario.almacenes.*'),
            'permission' => 'almacenes.manage',
            'icon' => 'M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 13h.01M15 9h.01M15 13h.01',
        ],
        [
            'label' => 'Usuarios',
            'href' => route('usuarios.index'),
            'active' => request()->routeIs('usuarios.*'),
            'permission' => 'users.view',
            'icon' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M22 21v-2a4 4 0 0 0-3-3.87',
        ],
        [
            'label' => 'Roles y Permisos',
            'href' => route('roles.index'),
            'active' => request()->routeIs('roles.*'),
            'permission' => 'roles.view',
            'icon' => 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z',
        ],
        [
            'label' => 'Auditoría',
            'href' => route('auditoria.index'),
            'active' => request()->routeIs('auditoria.*'),
            'permission' => 'platform.logs.view',
            'icon' => 'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 12h6M9 16h6',
        ],
        [
            'label' => 'Automatización',
            'href' => route('automatizacion.index'),
            'active' => request()->routeIs('automatizacion.*'),
            'permission' => 'automatizacion.view',
            'icon' => 'M13 2 3 14h7l-1 8 10-12h-7l1-8z',
        ],
        [
            'label' => 'Diagrama E-R',
            'href' => route('diagrama-er.index'),
            'active' => request()->routeIs('diagrama-er.*'),
            'permission' => null,
            'super_admin' => true,
            'icon' => 'M4 5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5zm10 0a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1V5zM4 15a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4zm10 0a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1v-4zM10 7h4m-7 3v4m7 0v-4m-4 7h4',
        ],
    ];

    $filterNav = static fn (array $items): array => array_values(array_filter(
        $items,
        static fn (array $item): bool => ($item['permission'] === null || $user->hasPermission($item['permission']))
            && (! ($item['super_admin'] ?? false) || $user->isSuperAdmin()),
    ));

    $operationItems = $filterNav($operationItems);
    $adminItems = $filterNav($adminItems);
@endphp

{{-- Backdrop para móvil --}}
<div
    x-cloak
    x-show="sidebarOpen"
    x-transition.opacity.duration.200ms
    @click="sidebarOpen = false"
    class="fixed inset-0 z-30 bg-black/60 lg:hidden"
    aria-hidden="true"
></div>

<aside
    :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-full',
        sidebarCollapsed ? 'lg:w-20' : 'lg:w-72',
    ]"
    class="fixed inset-y-0 left-0 z-40 flex w-72 max-w-[85vw] flex-col border-r-[3px] border-black bg-[var(--color-surface)] transition-[transform,width] duration-200 ease-out lg:translate-x-0"
    aria-label="Navegación principal"
>
    {{-- Botón para ocultar/mostrar el sidebar --}}
    <button
        type="button"
        x-cloak
        @click="toggleSidebarCollapsed()"
        class="brutal-sidebar-toggle hidden lg:flex"
        :aria-expanded="(!sidebarCollapsed).toString()"
        :title="sidebarCollapsed ? 'Mostrar menú' : 'Ocultar menú'"
        aria-controls="sidebar-nav"
        aria-label="Mostrar u ocultar el menú lateral"
    >
        <svg
            class="h-4 w-4 transition-transform duration-200"
            :class="sidebarCollapsed ? 'rotate-180' : ''"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
        >
            <path d="M15 6l-6 6 6 6" />
        </svg>
    </button>

    {{-- Marca --}}
    <div
        class="flex h-20 shrink-0 items-center border-b-[3px] border-black px-4"
        :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
    >
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 overflow-hidden" :title="sidebarCollapsed ? 'Factus OS — Control panel' : null">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center border-[3px] border-black bg-[var(--color-surface-muted)]">
                <x-application-logo class="h-7 w-7 fill-current text-[var(--color-primary)]" />
            </div>

            <div x-show="!sidebarCollapsed" x-transition.opacity.duration.150ms class="min-w-0">
                <p class="brutal-kicker text-[var(--color-text-soft)]">Factus OS</p>
                <p class="font-display text-lg font-black uppercase leading-none">Control panel</p>
            </div>
        </a>
    </div>

    {{-- Navegación --}}
    <nav id="sidebar-nav" class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        <p x-show="!sidebarCollapsed" x-transition.opacity.duration.150ms class="brutal-kicker px-2 pb-1 text-[var(--color-text-soft)]">Operación</p>

        @foreach ($operationItems as $item)
            @include('layouts.partials.sidebar-item', ['item' => $item])
        @endforeach

        @if (count($adminItems) > 0)
            <p x-show="!sidebarCollapsed" x-transition.opacity.duration.150ms class="brutal-kicker px-2 pb-1 pt-4 text-[var(--color-text-soft)]">Administración</p>

            @foreach ($adminItems as $item)
                @include('layouts.partials.sidebar-item', ['item' => $item])
            @endforeach
        @endif
    </nav>

    {{-- Usuario --}}
    <div class="shrink-0 border-t-[3px] border-black p-4" :class="sidebarCollapsed ? 'lg:px-2' : ''">
        <div class="mb-3 flex items-center gap-3" :class="sidebarCollapsed ? 'lg:justify-center' : ''">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center border-[3px] border-black bg-[var(--color-ink)] font-display text-sm font-black uppercase text-[var(--color-surface)]" :title="sidebarCollapsed ? @js($user->name) : null">
                {{ strtoupper(Str::substr($user->name, 0, 1)) }}
            </div>

            <div x-show="!sidebarCollapsed" x-transition.opacity.duration.150ms class="min-w-0">
                <p class="truncate text-sm font-bold">{{ $user->name }}</p>
                <p class="truncate text-[10px] uppercase tracking-[0.18em] text-[var(--color-text-soft)]">
                    {{ $user->isSuperAdmin() ? 'super admin' : 'operador' }}
                </p>
            </div>
        </div>

        <div class="flex gap-2" :class="sidebarCollapsed ? 'lg:flex-col lg:gap-1' : ''">
            <a
                href="{{ route('profile.edit') }}"
                class="brutal-button flex-1 px-3 py-2 text-[10px]"
                :title="sidebarCollapsed ? 'Perfil' : null"
            >
                <span x-show="!sidebarCollapsed">Perfil</span>
                <svg x-show="sidebarCollapsed" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8" />
                </svg>
            </a>

            <form method="POST" action="{{ route('logout') }}" class="flex-1">
                @csrf

                <button type="submit" class="brutal-button w-full px-3 py-2 text-[10px]" :title="sidebarCollapsed ? 'Salir' : null">
                    <span x-show="!sidebarCollapsed">Salir</span>
                    <svg x-show="sidebarCollapsed" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" />
                    </svg>
                </button>
            </form>

            <x-theme-toggle />
        </div>
    </div>
</aside>
