import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                bg: 'var(--color-bg)',
                surface: 'var(--color-surface)',
                ink: 'var(--color-ink)',
                primary: 'var(--color-primary)',
                accent: 'var(--color-accent)',
                success: 'var(--color-success)',
                danger: 'var(--color-danger)',
                muted: 'var(--color-muted)',
            },
            fontFamily: {
                sans: ['var(--font-body)', ...defaultTheme.fontFamily.sans],
                display: ['var(--font-display)', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                'brutal-blue': 'var(--shadow-brutal-blue)',
                'brutal-amber': 'var(--shadow-brutal-amber)',
                'brutal-black': 'var(--shadow-brutal-black)',
            },
            letterSpacing: {
                brutal: 'var(--tracking-label)',
            },
            borderWidth: {
                3: '3px',
            },
        },
    },

    plugins: [forms],
};
