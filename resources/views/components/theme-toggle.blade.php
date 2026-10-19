@props([
    'label' => true,
])

{{-- Botón compacto de cambio de tema (claro/oscuro). --}}
<button
    type="button"
    x-data
    @click="$store.theme.toggle()"
    class="group inline-flex h-10 w-10 shrink-0 items-center justify-center border-[3px] border-[var(--color-ink)] bg-[var(--color-ink)] text-[var(--color-surface)] transition-transform duration-150 hover:-translate-x-0.5 hover:-translate-y-0.5"
    :title="$store.theme.current === 'dark' ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro'"
    :aria-label="$store.theme.current === 'dark' ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro'"
    :aria-pressed="($store.theme.current === 'dark').toString()"
>
    {{-- Sol: visible en tema claro (acción: pasar a oscuro). --}}
    <svg
        x-show="$store.theme.current !== 'dark'"
        class="h-5 w-5"
        viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
        aria-hidden="true"
    >
        <circle cx="12" cy="12" r="4" />
        <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />
    </svg>

    {{-- Luna: visible en tema oscuro (acción: pasar a claro). --}}
    <svg
        x-show="$store.theme.current === 'dark'"
        x-cloak
        class="h-5 w-5"
        viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
        aria-hidden="true"
    >
        <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z" />
    </svg>
</button>
