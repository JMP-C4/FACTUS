@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => true,
])

@php
    $variants = [
        'success' => ['bg-success', 'text-white'],
        'error' => ['bg-danger', 'text-white'],
        'danger' => ['bg-danger', 'text-white'],
        'warning' => ['bg-accent', 'text-ink'],
        'info' => ['bg-primary', 'text-white'],
        'neutral' => ['bg-surface', 'text-ink'],
    ];

    [$background, $foreground] = $variants[$type] ?? $variants['info'];
@endphp

<div
    @if ($dismissible)
        x-data="{ visible: true }"
        x-show="visible"
        x-transition.opacity.duration.200ms
    @endif
    role="status"
    {{ $attributes->merge(['class' => "brutal-toast {$background} {$foreground}"]) }}
>
    <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M12 16v-4M12 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
    </svg>

    <div class="min-w-0 flex-1">
        @if ($title)
            <p class="font-display text-xs font-black uppercase tracking-brutal">{{ $title }}</p>
        @endif

        @if ($slot->isNotEmpty())
            <div class="{{ $title ? 'mt-1' : '' }}">{{ $slot }}</div>
        @endif
    </div>

    @if ($dismissible)
        <button
            type="button"
            @click="visible = false"
            class="shrink-0 opacity-80 transition-opacity hover:opacity-100"
            aria-label="Cerrar aviso"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                <path d="M18 6 6 18M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>
