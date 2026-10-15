@props([
    'isSuperAdmin' => false,
])

{{-- Encabezado reutilizable del dashboard.
     Mantiene separados el contexto visual y los badges de estado del resto de la página. --}}
<div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
    <div class="space-y-3">
        <p class="brutal-kicker text-[var(--color-text-soft)]">Control center</p>
        <h2 class="text-3xl font-black uppercase leading-none text-[var(--color-ink)]">
            {{ $isSuperAdmin ? 'Super Admin Dashboard' : 'Dashboard' }}
        </h2>
    </div>

    <div class="flex flex-wrap gap-3">
        <span class="brutal-badge bg-[rgba(37,99,235,0.15)] text-[var(--color-ink)]">
            {{ $isSuperAdmin ? 'global scope' : 'operational scope' }}
        </span>
        <span class="brutal-badge bg-[rgba(245,158,11,0.18)] text-[var(--color-ink)]">
            sandbox ready
        </span>
    </div>
</div>
