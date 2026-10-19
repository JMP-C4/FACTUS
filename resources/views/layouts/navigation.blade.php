<header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b-[3px] border-black bg-[var(--color-surface)] px-4 lg:hidden">
    <button
        @click="sidebarOpen = ! sidebarOpen"
        class="flex h-11 w-11 shrink-0 items-center justify-center border-[3px] border-black bg-[var(--color-surface-muted)] text-[var(--color-ink)]"
        aria-label="Abrir menú de navegación"
        :aria-expanded="sidebarOpen.toString()"
    >
        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <path :class="{ 'hidden': sidebarOpen, 'inline-flex': ! sidebarOpen }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            <path :class="{ 'hidden': ! sidebarOpen, 'inline-flex': sidebarOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-2">
        <span class="font-display text-sm font-black uppercase leading-none">Factus OS</span>
    </a>

    <div class="ml-auto flex items-center gap-2">
        <x-theme-toggle />

        <div class="flex h-10 w-10 shrink-0 items-center justify-center border-[3px] border-black bg-[var(--color-ink)] font-display text-xs font-black uppercase text-[var(--color-surface)]">
            {{ strtoupper(Str::substr(Auth::user()->name, 0, 1)) }}
        </div>
    </div>
</header>
