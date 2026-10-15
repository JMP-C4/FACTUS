<nav x-data="{ open: false }" class="border-b-[3px] border-black bg-[var(--color-surface)]">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-20 items-center justify-between gap-6">
            <div class="flex items-center gap-8">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center border-[3px] border-black bg-[var(--color-surface-muted)]">
                        <x-application-logo class="h-7 w-7 fill-current text-[var(--color-primary)]" />
                    </div>

                    <div class="hidden sm:block">
                        <p class="brutal-kicker text-[var(--color-text-soft)]">Factus OS</p>
                        <p class="font-display text-xl font-black uppercase leading-none">Control panel</p>
                    </div>
                </a>

                <div class="hidden items-center gap-2 lg:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>

                    @if(Auth::user()->hasPermission('pos.vender'))
                        <x-nav-link :href="route('facturacion.pos')" :active="request()->routeIs('facturacion.pos')">
                            POS
                        </x-nav-link>
                    @endif

                    @if(Auth::user()->hasPermission('facturacion.view'))
                        <x-nav-link :href="route('facturacion.facturas.index')" :active="request()->routeIs('facturacion.facturas.*')">
                            Facturas
                        </x-nav-link>
                    @endif

                    @if(Auth::user()->hasPermission('productos.view'))
                        <x-nav-link :href="route('productos.index')" :active="request()->routeIs('productos.*')">
                            Productos
                        </x-nav-link>
                    @endif

                    @if(Auth::user()->hasPermission('clientes.view'))
                        <x-nav-link :href="route('clientes.index')" :active="request()->routeIs('clientes.*')">
                            Clientes
                        </x-nav-link>
                    @endif

                    @if(Auth::user()->hasPermission('inventario.view'))
                        <x-nav-link :href="route('inventario.movimientos.index')" :active="request()->routeIs('inventario.*')">
                            Inventario
                        </x-nav-link>
                    @endif

                    @if(Auth::user()->hasPermission('reportes.ventas'))
                        <x-nav-link :href="route('reportes.ventas.index')" :active="request()->routeIs('reportes.*')">
                            Reportes
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden items-center gap-4 sm:flex">
                <div class="text-right">
                    <p class="text-sm font-semibold text-[var(--color-ink)]">{{ Auth::user()->name }}</p>
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">
                        {{ Auth::user()->isSuperAdmin() ? 'super admin' : 'authenticated user' }}
                    </p>
                </div>

                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="brutal-button px-4 py-2 text-xs">
                            Menú
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            Profile
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Log Out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="flex items-center sm:hidden">
                <button
                    @click="open = ! open"
                    class="flex h-11 w-11 items-center justify-center border-[3px] border-black bg-[var(--color-surface-muted)] text-[var(--color-ink)]"
                >
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t-[2px] border-black sm:hidden">
        <div class="space-y-1 px-4 py-4">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-responsive-nav-link>

            @if(Auth::user()->hasPermission('pos.vender'))
                <x-responsive-nav-link :href="route('facturacion.pos')" :active="request()->routeIs('facturacion.pos')">
                    POS
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->hasPermission('facturacion.view'))
                <x-responsive-nav-link :href="route('facturacion.facturas.index')" :active="request()->routeIs('facturacion.facturas.*')">
                    Facturas
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->hasPermission('productos.view'))
                <x-responsive-nav-link :href="route('productos.index')" :active="request()->routeIs('productos.*')">
                    Productos
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->hasPermission('clientes.view'))
                <x-responsive-nav-link :href="route('clientes.index')" :active="request()->routeIs('clientes.*')">
                    Clientes
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->hasPermission('inventario.view'))
                <x-responsive-nav-link :href="route('inventario.movimientos.index')" :active="request()->routeIs('inventario.*')">
                    Inventario
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->hasPermission('reportes.ventas'))
                <x-responsive-nav-link :href="route('reportes.ventas.index')" :active="request()->routeIs('reportes.*')">
                    Reportes
                </x-responsive-nav-link>
            @endif
        </div>

        <div class="border-t-[2px] border-black px-4 py-4">
            <div class="mb-3">
                <div class="font-medium text-base text-[var(--color-ink)]">{{ Auth::user()->name }}</div>
                <div class="text-sm text-[var(--color-text-soft)]">{{ Auth::user()->email }}</div>
            </div>

            <div class="space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    Profile
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        Log Out
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
