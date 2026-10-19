<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Nuevo Producto</h2>
            <a href="{{ route('productos.index') }}" class="brutal-button">
                Volver
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('productos.store') }}">
            @csrf

            <div class="brutal-card space-y-6">
                {{-- Datos básicos --}}
                <div>
                    <h3 class="brutal-kicker mb-4 text-sm">Datos Básicos</h3>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="name" class="brutal-label mb-1 block">Nombre *</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                class="brutal-input"
                                required
                            >
                            @error('name')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="description" class="brutal-label mb-1 block">Descripción</label>
                            <textarea
                                id="description"
                                name="description"
                                rows="3"
                                class="brutal-input"
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="category_id" class="brutal-label mb-1 block">Categoría</label>
                            <select id="category_id" name="category_id" class="brutal-input">
                                <option value="">Sin categoría</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="brand_id" class="brutal-label mb-1 block">Marca</label>
                            <select id="brand_id" name="brand_id" class="brutal-input">
                                <option value="">Sin marca</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('brand_id')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Identificadores --}}
                <div>
                    <h3 class="brutal-kicker mb-4 text-sm">Identificadores</h3>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="sku" class="brutal-label mb-1 block">SKU *</label>
                            <input
                                type="text"
                                id="sku"
                                name="sku"
                                value="{{ old('sku') }}"
                                class="brutal-input"
                                required
                            >
                            @error('sku')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="barcode" class="brutal-label mb-1 block">Código de Barras</label>
                            <input
                                type="text"
                                id="barcode"
                                name="barcode"
                                value="{{ old('barcode', request('barcode')) }}"
                                class="brutal-input"
                            >
                            @error('barcode')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Datos fiscales --}}
                <div>
                    <h3 class="brutal-kicker mb-4 text-sm">Datos Fiscales</h3>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label for="unit_measure_code" class="brutal-label mb-1 block">Unidad de Medida *</label>
                            <input
                                type="text"
                                id="unit_measure_code"
                                name="unit_measure_code"
                                value="{{ old('unit_measure_code', '94') }}"
                                class="brutal-input"
                                required
                            >
                            @error('unit_measure_code')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="standard_code" class="brutal-label mb-1 block">Código Estándar</label>
                            <input
                                type="text"
                                id="standard_code"
                                name="standard_code"
                                value="{{ old('standard_code') }}"
                                class="brutal-input"
                            >
                            @error('standard_code')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="price" class="brutal-label mb-1 block">Precio *</label>
                            <input
                                type="number"
                                id="price"
                                name="price"
                                value="{{ old('price', '0') }}"
                                step="0.01"
                                min="0"
                                class="brutal-input"
                                required
                            >
                            @error('price')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-data="{ rate: '{{ old('tax_rate', $taxes->first()?->rate ?? 0) }}' }">
                            <label for="tax_id" class="brutal-label mb-1 block">Impuesto (IVA) *</label>
                            @if($taxes->isEmpty())
                                <input
                                    type="number"
                                    id="tax_rate"
                                    name="tax_rate"
                                    value="{{ old('tax_rate', 19) }}"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    class="brutal-input"
                                    required
                                >
                            @else
                                <select
                                    id="tax_id"
                                    name="tax_id"
                                    class="brutal-input"
                                    x-on:change="rate = $event.target.selectedOptions[0]?.dataset.rate ?? rate"
                                >
                                    <option value="">Sin impuesto</option>
                                    @foreach($taxes as $tax)
                                        <option
                                            value="{{ $tax->id }}"
                                            data-rate="{{ $tax->rate }}"
                                            {{ (string) old('tax_id') === (string) $tax->id ? 'selected' : '' }}
                                        >
                                            {{ $tax->name }} ({{ number_format((float) $tax->rate, 2, ',', '.') }}%)
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="tax_rate" x-model="rate">
                            @endif
                            @error('tax_id')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                            @error('tax_rate')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="minimum_stock" class="brutal-label mb-1 block">Stock Mínimo *</label>
                            <input
                                type="number"
                                id="minimum_stock"
                                name="minimum_stock"
                                value="{{ old('minimum_stock', '0') }}"
                                min="0"
                                class="brutal-input"
                                required
                            >
                            @error('minimum_stock')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-end">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    {{ old('is_active', '1') === '1' ? 'checked' : '' }}
                                    class="w-4 h-4"
                                >
                                <span class="brutal-label">Activo</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="flex items-center justify-end gap-4 pt-4 border-t-[2px] border-ink">
                    <a href="{{ route('productos.index') }}" class="brutal-button">
                        Cancelar
                    </a>
                    <button type="submit" class="brutal-button !bg-primary !text-white">
                        Crear Producto
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
