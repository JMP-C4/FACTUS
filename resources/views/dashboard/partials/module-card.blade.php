@props([
    'title',
    'description',
    'metric',
    'status',
    'tone' => 'blue',
    'url' => null,
])

@php
    // Variante de color de la barra superior según el tono del módulo.
    $accent = match ($tone) {
        'amber' => 'var(--color-accent)',
        'green' => 'var(--color-success)',
        'red' => 'var(--color-danger)',
        default => 'var(--color-primary)',
    };

    $tag = $url ? 'a' : 'article';
@endphp

<{{ $tag }}
    @if ($url) href="{{ $url }}" @endif
    @if ($description) title="{{ $description }}" @endif
    class="brutal-card group flex flex-col justify-between !p-4 transition-transform duration-150 {{ $url ? 'hover:-translate-x-1 hover:-translate-y-1 focus-visible:outline-none' : '' }}"
>
    <div class="flex items-start justify-between gap-2">
        <p class="brutal-kicker text-[10px] text-[var(--color-text-soft)]">{{ $title }}</p>
        <span class="h-2.5 w-2.5 shrink-0 border-2 border-black" style="background-color: {{ $accent }}"></span>
    </div>

    <p class="mt-3 font-display text-xl font-black uppercase leading-none text-[var(--color-ink)]">
        {{ $metric }}
    </p>

    <div class="mt-3 flex items-center justify-between gap-2 border-t-[2px] border-black pt-2.5">
        <span class="brutal-badge bg-[rgba(17,17,17,0.06)] text-[9px]">{{ $status }}</span>

        @if ($url)
            <span class="flex h-7 w-7 items-center justify-center border-[2px] border-[var(--color-ink)] bg-[var(--color-ink)] text-[var(--color-surface)]">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
            </span>
        @endif
    </div>
</{{ $tag }}>
