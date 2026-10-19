@php
    // Mensajes de sesión que se muestran como toast (no bloqueantes).
    $boot = [];

    foreach (['success' => 'success', 'info' => 'info'] as $key => $type) {
        if (! session()->has($key)) {
            continue;
        }

        foreach ((array) session($key) as $message) {
            if (is_string($message) && $message !== '') {
                $boot[] = ['type' => $type, 'message' => $message, 'timeout' => 5000];
            }
        }
    }

    // Vía explícita: session('toast') acepta string o array de arreglos.
    if (session()->has('toast')) {
        foreach ((array) session('toast') as $item) {
            if (is_array($item)) {
                $boot[] = array_merge(['type' => 'info', 'timeout' => 5000], $item);
            } elseif (is_string($item) && $item !== '') {
                $boot[] = ['type' => 'info', 'message' => $item, 'timeout' => 5000];
            }
        }
    }
@endphp

<div
    x-data
    x-init="(@js($boot)).forEach((toast) => $store.toasts.push(toast))"
    class="pointer-events-none fixed inset-x-0 top-4 z-[60] flex flex-col items-end gap-3 px-4 sm:px-6"
    aria-live="polite"
    aria-atomic="true"
>
    <template x-for="toast in $store.toasts.items" :key="toast.id">
        @include('components.feedback.toast-item')
    </template>
</div>
