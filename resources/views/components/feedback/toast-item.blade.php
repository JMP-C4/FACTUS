<div
    class="brutal-toast pointer-events-auto w-full max-w-sm"
    :class="{
        'bg-success text-white': toast.type === 'success',
        'bg-danger text-white': toast.type === 'error' || toast.type === 'danger',
        'bg-accent text-ink': toast.type === 'warning',
        'bg-primary text-white': toast.type === 'info',
        'bg-surface text-ink': ! ['success', 'error', 'danger', 'warning', 'info'].includes(toast.type),
    }"
    role="status"
>
    <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M12 16v-4M12 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
    </svg>

    <div class="min-w-0 flex-1">
        <p x-show="toast.title" x-text="toast.title" class="font-display text-xs font-black uppercase tracking-brutal"></p>
        <p class="break-words" x-text="toast.message"></p>
    </div>

    <button
        type="button"
        @click="$store.toasts.dismiss(toast.id)"
        class="shrink-0 opacity-80 transition-opacity hover:opacity-100"
        aria-label="Cerrar aviso"
    >
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
            <path d="M18 6 6 18M6 6l12 12" />
        </svg>
    </button>
</div>
