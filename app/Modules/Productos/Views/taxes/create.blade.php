<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Nuevo Impuesto</h2>
            <a href="{{ route('productos.impuestos.index') }}" class="brutal-button">
                Volver
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('productos.impuestos.store') }}" class="brutal-card space-y-6">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="brutal-label mb-1 block">Nombre *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" class="brutal-input" required>
                    @error('name')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="code" class="brutal-label mb-1 block">Código *</label>
                    <input type="text" id="code" name="code" value="{{ old('code') }}" class="brutal-input" placeholder="IVA19" required>
                    @error('code')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="rate" class="brutal-label mb-1 block">Tarifa (%) *</label>
                    <input type="number" id="rate" name="rate" value="{{ old('rate', 19) }}" step="0.01" min="0" max="100" class="brutal-input" required>
                    @error('rate')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="type" class="brutal-label mb-1 block">Tipo *</label>
                    <select id="type" name="type" class="brutal-input" required>
                        <option value="vat" {{ old('type') === 'vat' ? 'selected' : '' }}>IVA (gravado)</option>
                        <option value="exempt" {{ old('type') === 'exempt' ? 'selected' : '' }}>Exento (0%)</option>
                        <option value="excluded" {{ old('type') === 'excluded' ? 'selected' : '' }}>Excluido de IVA</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="brutal-label mb-1 block">Descripción</label>
                    <input type="text" id="description" name="description" value="{{ old('description') }}" class="brutal-input">
                    @error('description')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-end">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') === '1' ? 'checked' : '' }} class="w-4 h-4">
                        <span class="brutal-label">Activo</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-4 border-t-[2px] border-ink">
                <a href="{{ route('productos.impuestos.index') }}" class="brutal-button">Cancelar</a>
                <button type="submit" class="brutal-button !bg-primary !text-white">Crear Impuesto</button>
            </div>
        </div>
    </form>
</x-app-layout>
