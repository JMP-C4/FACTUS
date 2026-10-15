<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Nuevo Ajuste de Stock</h2>
            <a href="{{ route('inventario.movimientos.index') }}" class="brutal-button">
                Volver
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('inventario.ajustes.store') }}">
            @csrf

            <div class="brutal-card space-y-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="warehouse_id" class="brutal-label mb-1 block">Almacén *</label>
                        <select id="warehouse_id" name="warehouse_id" class="brutal-input" required>
                            <option value="">Seleccionar almacén</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('warehouse_id')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="product_id" class="brutal-label mb-1 block">Producto *</label>
                        <input
                            type="number"
                            id="product_id"
                            name="product_id"
                            value="{{ old('product_id') }}"
                            class="brutal-input"
                            placeholder="ID del producto"
                            required
                        >
                        @error('product_id')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="new_quantity" class="brutal-label mb-1 block">Nueva Cantidad *</label>
                        <input
                            type="number"
                            id="new_quantity"
                            name="new_quantity"
                            value="{{ old('new_quantity', '0') }}"
                            min="0"
                            class="brutal-input"
                            required
                        >
                        @error('new_quantity')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="reason" class="brutal-label mb-1 block">Motivo *</label>
                        <input
                            type="text"
                            id="reason"
                            name="reason"
                            value="{{ old('reason') }}"
                            class="brutal-input"
                            placeholder="Ej: Conteo físico"
                            required
                        >
                        @error('reason')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4 pt-4 border-t-[2px] border-ink">
                    <a href="{{ route('inventario.movimientos.index') }}" class="brutal-button">
                        Cancelar
                    </a>
                    <button type="submit" class="brutal-button !bg-accent !text-ink">
                        Registrar Ajuste
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
