<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Productos</h2>
            <a href="{{ route('productos.create') }}" class="brutal-button">
                Nuevo Producto
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Filtros --}}
        <div class="brutal-card mb-6">
            <form method="GET" action="{{ route('productos.index') }}" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="brutal-label mb-1 block">Buscar</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Nombre, SKU o descripción..."
                        class="brutal-input"
                    >
                </div>

                <div class="min-w-[150px]">
                    <label class="brutal-label mb-1 block">Categoría</label>
                    <select name="category_id" class="brutal-input">
                        <option value="">Todas</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[150px]">
                    <label class="brutal-label mb-1 block">Marca</label>
                    <select name="brand_id" class="brutal-input">
                        <option value="">Todas</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[120px]">
                    <label class="brutal-label mb-1 block">Estado</label>
                    <select name="is_active" class="brutal-input">
                        <option value="">Todos</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Activos</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="brutal-button">
                        Filtrar
                    </button>
                </div>
            </form>
        </div>

        {{-- Tabla de productos --}}
        <div class="brutal-card overflow-x-auto">
            <table class="brutal-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>SKU</th>
                        <th>Categoría</th>
                        <th>Marca</th>
                        <th>Precio</th>
                        <th>Impuesto</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td class="font-semibold">
                                <a href="{{ route('productos.show', $product) }}" class="hover:text-primary">
                                    {{ $product->name }}
                                </a>
                            </td>
                            <td class="font-mono text-xs">{{ $product->sku }}</td>
                            <td>{{ $product->category?->name ?? '—' }}</td>
                            <td>{{ $product->brand?->name ?? '—' }}</td>
                            <td class="font-mono">${{ number_format($product->price, 2, ',', '.') }}</td>
                            <td class="font-mono">{{ $product->tax_rate }}%</td>
                            <td>
                                @if($product->is_active)
                                    <span class="brutal-badge bg-success text-white">Activo</span>
                                @else
                                    <span class="brutal-badge bg-muted text-white">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a
                                        href="{{ route('productos.edit', $product) }}"
                                        class="brutal-button !px-3 !py-1.5 !text-[10px]"
                                    >
                                        Editar
                                    </a>
                                    <form
                                        method="POST"
                                        action="{{ route('productos.toggleActive', $product) }}"
                                        class="inline"
                                    >
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            type="submit"
                                            class="brutal-button !px-3 !py-1.5 !text-[10px] {{ $product->is_active ? '!bg-danger !text-white' : '!bg-success !text-white' }}"
                                        >
                                            {{ $product->is_active ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-muted">
                                No se encontraron productos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="mt-6">
            {{ $products->withQueryString()->links() }}
        </div>
    </div>
</x-app-layout>
