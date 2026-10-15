@props([
    'user',
])

{{-- Sidebar administrativa.
     Centraliza navegación interna del dashboard y contexto del usuario autenticado. --}}
<aside class="brutal-card-accent h-fit xl:sticky xl:top-8">
    <div class="space-y-8">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <p class="brutal-kicker text-[var(--color-text-soft)]">Navigation</p>
                <div class="h-9 w-9 border-[3px] border-black bg-[var(--color-primary)]"></div>
            </div>

            <div class="rounded-none border-[2px] border-black bg-white px-4 py-3">
                <p class="text-sm font-semibold text-[var(--color-text-muted)]">Conectado como</p>
                <p class="mt-2 font-display text-xl font-black uppercase">{{ $user->name }}</p>
                <p class="mt-1 text-sm text-[var(--color-text-muted)]">{{ $user->email }}</p>
            </div>
        </div>

        <nav class="space-y-2">
            <a href="#usuarios" class="brutal-nav-item">
                <span class="h-3 w-3 border-2 border-black bg-[var(--color-primary)]"></span>
                <span>Usuarios</span>
            </a>
            <a href="#roles" class="brutal-nav-item">
                <span class="h-3 w-3 border-2 border-black bg-[var(--color-accent)]"></span>
                <span>Roles y permisos</span>
            </a>
            <a href="#companias" class="brutal-nav-item">
                <span class="h-3 w-3 border-2 border-black bg-[var(--color-success)]"></span>
                <span>Compañías</span>
            </a>
            <a href="#facturacion" class="brutal-nav-item">
                <span class="h-3 w-3 border-2 border-black bg-[var(--color-danger)]"></span>
                <span>Facturación local</span>
            </a>
            <a href="#logs" class="brutal-nav-item">
                <span class="h-3 w-3 border-2 border-black bg-black"></span>
                <span>Logs del sistema</span>
            </a>
        </nav>

        <div class="brutal-alert bg-[rgba(37,99,235,0.12)]">
            <div class="mt-1 h-3 w-3 shrink-0 border-2 border-black bg-[var(--color-primary)]"></div>
            <div>
                <p class="font-display text-sm font-black uppercase">Momento actual</p>
                <p class="mt-1 text-sm leading-6 text-[var(--color-text-muted)]">
                    El panel ya refleja RBAC, bootstrap del super admin y emisión de facturación local.
                </p>
            </div>
        </div>
    </div>
</aside>
