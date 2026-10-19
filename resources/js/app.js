import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

/**
 * Global toast store.
 *
 * Usage from JavaScript:
 *   window.notify('Guardado correctamente', { type: 'success' });
 *   window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Falló' } }));
 *
 * Usage from Alpine/Blade:
 *   $dispatch('toast', { type: 'info', message: 'Hola' })
 *   $store.toasts.push({ type: 'warning', title: 'Atención', message: 'Stock bajo' })
 */
/**
 * Global theme (dark mode) store.
 *
 * Persists the user's choice in localStorage under `factus.theme` and toggles
 * the `dark` class on <html>. The initial class is applied by an inline script
 * in the layout head to avoid a flash of the wrong theme.
 */
Alpine.store('theme', {
    current: document.documentElement.classList.contains('dark') ? 'dark' : 'light',

    toggle() {
        this.current = this.current === 'dark' ? 'light' : 'dark';
        document.documentElement.classList.toggle('dark', this.current === 'dark');
        localStorage.setItem('factus.theme', this.current);

        return this.current;
    },
});

Alpine.store('toasts', {
    items: [],

    push(detail = {}) {
        const toast = Object.assign(
            {
                id: `${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 8)}`,
                type: 'info',
                title: null,
                message: '',
                timeout: 5000,
            },
            detail,
        );

        if (!toast.message && !toast.title) {
            return null;
        }

        this.items.push(toast);

        if (toast.timeout) {
            setTimeout(() => this.dismiss(toast.id), toast.timeout);
        }

        return toast.id;
    },

    dismiss(id) {
        this.items = this.items.filter((toast) => toast.id !== id);
    },

    clear() {
        this.items = [];
    },
});

window.notify = (message, options = {}) =>
    window.Alpine.store('toasts').push(Object.assign({ message }, options));

window.addEventListener('toast', (event) => {
    window.Alpine.store('toasts').push(event.detail || {});
});

Alpine.start();
