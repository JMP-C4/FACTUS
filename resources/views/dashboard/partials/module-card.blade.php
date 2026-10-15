@props([
    'title',
    'description',
    'metric',
    'status',
    'tone' => 'blue',
])

@php
    // Definimos variantes de color para reutilizar el mismo bloque
    // en secciones administrativas distintas.
    $variantClasses = match ($tone) {
        'amber' => 'bg-[rgba(245,158,11,0.18)]',
        'green' => 'bg-[rgba(22,163,74,0.18)]',
        'red' => 'bg-[rgba(220,38,38,0.18)]',
        default => 'bg-[rgba(37,99,235,0.15)]',
    };
@endphp

<article class="brutal-card h-full">
    <div class="mb-5 flex items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="brutal-kicker text-[var(--color-text-soft)]">{{ $title }}</p>
            <p class="max-w-sm text-sm leading-6 text-[var(--color-text-muted)]">
                {{ $description }}
            </p>
        </div>

        <span class="brutal-badge {{ $variantClasses }}">
            {{ $status }}
        </span>
    </div>

    <div class="flex items-end justify-between gap-4 border-t-[2px] border-black pt-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-text-soft)]">
                foco actual
            </p>
            <p class="mt-2 font-display text-2xl font-black uppercase">{{ $metric }}</p>
        </div>

        <div class="h-10 w-10 border-[3px] border-black bg-[var(--color-surface-muted)]"></div>
    </div>
</article>
