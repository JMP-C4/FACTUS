<x-app-layout>
    <x-slot name="header">
        @include('dashboard.partials.header', [
            'isSuperAdmin' => $isSuperAdmin,
        ])
    </x-slot>

    @include('dashboard.partials.super-admin-shell', [
        'user' => auth()->user(),
        'summary' => $summary,
        'modules' => $modules,
        'logRows' => $logRows,
    ])
</x-app-layout>
