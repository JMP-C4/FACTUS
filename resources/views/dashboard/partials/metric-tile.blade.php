@props([
    'label',
    'value',
    'caption',
    'tone' => 'blue',
])

@php
    // Sombra por tono para diferenciar los KPIs sin romper el estilo brutalista.
    $shadowClass = match ($tone) {
        'amber' => 'shadow-brutal-amber',
        'black' => 'shadow-brutal-black',
        default => 'shadow-brutal-blue',
    };
@endphp

<article class="brutal-stat {{ $shadowClass }} !min-h-0">
    <div class="space-y-1.5">
        <p class="brutal-kicker text-[10px] text-[var(--color-text-soft)]">{{ $label }}</p>
        <p class="font-display text-3xl font-black uppercase leading-none">{{ $value }}</p>
    </div>

    <p class="mt-3 text-xs leading-5 text-[var(--color-text-muted)]">
        {{ $caption }}
    </p>
</article>
