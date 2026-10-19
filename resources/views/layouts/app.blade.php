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
        <div
            x-data="{
                sidebarOpen: false,
                sidebarCollapsed: localStorage.getItem('factus.sidebar.collapsed') === 'true',
                toggleSidebarCollapsed() {
                    this.sidebarCollapsed = !this.sidebarCollapsed;
                    localStorage.setItem('factus.sidebar.collapsed', this.sidebarCollapsed ? 'true' : 'false');
                },
            }"
            class="min-h-screen bg-bg text-ink"
        >
            @include('layouts.sidebar')

            <div class="transition-[padding] duration-200 ease-out" :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-72'">
                @include('layouts.navigation')

                <!-- Page Heading -->
                @isset($header)
                    <header class="border-b-[3px] border-black bg-surface">
                        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Banners de sesión (errores/advertencias) -->
                <x-feedback.flash />

                <!-- Page Content -->
                <main class="brutal-page">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Toasts globales (avisos no bloqueantes) -->
        <x-feedback.toast-host />
    </body>
</html>
