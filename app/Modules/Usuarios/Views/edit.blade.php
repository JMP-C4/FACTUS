<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Editar Usuario</h2>
            <a href="{{ route('usuarios.index') }}" class="brutal-button">Volver</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('usuarios.update', $user) }}">
            @csrf
            @method('PUT')
            @include('usuarios::_form', ['user' => $user, 'submitLabel' => 'Actualizar Usuario'])
        </form>
    </div>
</x-app-layout>
