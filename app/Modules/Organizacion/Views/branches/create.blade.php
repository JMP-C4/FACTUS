<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Nueva Sucursal</h2>
            <a href="{{ route('sucursales.index') }}" class="brutal-button">Volver</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('sucursales.store') }}">
            @csrf
            @include('organizacion::branches._form', ['branch' => null, 'companies' => $companies, 'submitLabel' => 'Crear Sucursal'])
        </form>
    </div>
</x-app-layout>
