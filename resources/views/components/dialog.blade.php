@props([
    'name',
    'show' => false,
    'title' => null,
    'subtitle' => null,
    'maxWidth' => 'lg',
    'closeable' => true,
])

@php
    $maxWidth = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
    ][$maxWidth] ?? 'sm:max-w-lg';
@endphp

<div
    x-data="{ show: @js($show) }"
    x-init="$watch('show', (value) => document.body.classList.toggle('overflow-hidden', value))"
    x-on:open-dialog.window="if ($event.detail === '{{ $name }}') show = true"
    x-on:close-dialog.window="if ($event.detail === '{{ $name }}') show = false"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-[70] flex items-start justify-center overflow-y-auto px-4 py-10 sm:py-16"
    role="dialog"
    aria-modal="true"
    aria-label="{{ $title ?? $name }}"
>
    <div
        x-show="show"
        x-transition.opacity.duration.200ms
        @if ($closeable) x-on:click="show = false" @endif
        class="fixed inset-0 bg-black/60"
        aria-hidden="true"
    ></div>

    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        class="brutal-dialog-panel relative w-full {{ $maxWidth }}"
    >
        <div class="brutal-dialog-head">
            <div class="min-w-0">
                <h3 class="font-display text-sm font-black uppercase tracking-brutal">{{ $title ?? '' }}</h3>
                @if ($subtitle)
                    <p class="mt-1 text-xs text-white/70">{{ $subtitle }}</p>
                @endif
            </div>

            @if ($closeable)
                <button
                    type="button"
                    x-on:click="show = false"
                    class="shrink-0 opacity-80 transition-opacity hover:opacity-100"
                    aria-label="Cerrar"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg>
                </button>
            @endif
        </div>

        <div class="px-5 py-5">
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="flex items-center justify-end gap-3 border-t-[3px] border-ink bg-surface px-5 py-4">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
