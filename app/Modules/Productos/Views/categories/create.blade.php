<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Nueva Categoría</h2>
            <a href="{{ route('productos.categorias.index') }}" class="brutal-button">
                Volver
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('productos.categorias.store') }}" class="brutal-card space-y-6">
            @csrf

            <div>
                <label for="name" class="brutal-label mb-1 block">Nombre *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" class="brutal-input" required>
                @error('name')
                    <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') === '1' ? 'checked' : '' }} class="w-4 h-4">
                <span class="brutal-label">Activa</span>
            </label>

            <div class="flex items-center justify-end gap-4 pt-4 border-t-[2px] border-ink">
                <a href="{{ route('productos.categorias.index') }}" class="brutal-button">Cancelar</a>
                <button type="submit" class="brutal-button !bg-primary !text-white">Crear Categoría</button>
            </div>
        </form>
    </div>
</x-app-layout>
