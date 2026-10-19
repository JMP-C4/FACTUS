<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Nuevo Usuario</h2>
            <a href="{{ route('usuarios.index') }}" class="brutal-button">Volver</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('usuarios.store') }}">
            @csrf
            @include('usuarios::_form', ['user' => null, 'submitLabel' => 'Crear Usuario'])
        </form>
    </div>
</x-app-layout>
