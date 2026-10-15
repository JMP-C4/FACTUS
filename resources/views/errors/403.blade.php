<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>403 - Acceso denegado</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,700|inter-tight:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="flex min-h-screen items-center justify-center bg-bg p-6 text-ink">
            <div class="brutal-card w-full max-w-lg text-center">
                <p class="brutal-kicker text-5xl">403</p>
                <h1 class="brutal-kicker mt-4 text-lg">Acceso denegado</h1>
                <p class="mt-3 text-sm text-muted">
                    {{ $message ?? 'No tienes permisos suficientes para acceder a este recurso.' }}
                </p>
                <div class="mt-6 flex justify-center gap-3">
                    <a href="{{ url()->previous() }}" class="brutal-button">Volver</a>
                    <a href="{{ route('dashboard') }}" class="brutal-button !bg-primary !text-white">
                        Ir al panel
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>
