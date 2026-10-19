<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,700|inter-tight:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>[x-cloak] { display: none !important; }</style>

        {{-- Aplica el tema antes del primer render para evitar parpadeo. --}}
        <script>
            (function () {
                try {
                    const stored = localStorage.getItem('factus.theme');
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    if (stored === 'dark' || (! stored && prefersDark)) {
                        document.documentElement.classList.add('dark');
                    }
                } catch (e) {}
            })();
        </script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-bg text-ink">
            <div class="fixed right-4 top-4 z-50">
                <x-theme-toggle />
            </div>

            <x-feedback.flash />

            {{ $slot }}
        </div>

        <x-feedback.toast-host />
    </body>
</html>
