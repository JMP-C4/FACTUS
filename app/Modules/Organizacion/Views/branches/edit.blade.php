<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Editar Sucursal</h2>
            <a href="{{ route('sucursales.index') }}" class="brutal-button">Volver</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('sucursales.update', $branch) }}">
            @csrf
            @method('PUT')
            @include('organizacion::branches._form', ['branch' => $branch, 'companies' => $companies, 'submitLabel' => 'Actualizar Sucursal'])
        </form>
    </div>
</x-app-layout>
