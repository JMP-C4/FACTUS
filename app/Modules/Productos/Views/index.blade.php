<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Productos</h2>
            <div class="flex items-center gap-2">
                @if(auth()->user()->hasPermission('categorias.manage'))
                    <a href="{{ route('productos.categorias.index') }}" class="brutal-button">
                        Categorías
                    </a>
                @endif
                @if(auth()->user()->hasPermission('marcas.manage'))
                    <a href="{{ route('productos.marcas.index') }}" class="brutal-button">
                        Marcas
                    </a>
                @endif
                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('productos.impuestos.index') }}" class="brutal-button">
                        Impuestos
                    </a>
                @endif
                @if(auth()->user()->hasPermission('productos.create'))
                    <a href="{{ route('productos.create') }}" class="brutal-button !bg-primary !text-white">
                        Nuevo Producto
                    </a>
                @endif
            </div>
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
                                        href="{{ route('productos.show', $product) }}"
                                        class="brutal-button !px-2.5 !py-1.5"
                                        title="Ver"
                                        aria-label="Ver {{ $product->name }}"
                                    >
                                        <x-action-icon name="view" />
                                    </a>
                                    <a
                                        href="{{ route('productos.edit', $product) }}"
                                        class="brutal-button !px-2.5 !py-1.5"
                                        title="Editar"
                                        aria-label="Editar {{ $product->name }}"
                                    >
                                        <x-action-icon name="edit" />
                                    </a>
                                    <x-confirm-dialog
                                        name="toggle-product-{{ $product->id }}"
                                        :title="$product->is_active ? 'Desactivar producto' : 'Activar producto'"
                                        :message="$product->is_active ? '¿Desactivar este producto? Dejará de estar disponible para nuevas ventas.' : '¿Activar este producto para su venta?'"
                                        :confirm-label="$product->is_active ? 'Desactivar' : 'Activar'"
                                        :variant="$product->is_active ? 'danger' : 'success'"
                                        action="{{ route('productos.toggleActive', $product) }}"
                                        method="PATCH"
                                    >
                                        <x-slot:trigger>
                                            <button
                                                type="button"
                                                class="brutal-button !px-2.5 !py-1.5 {{ $product->is_active ? '!bg-danger !text-white' : '!bg-success !text-white' }}"
                                                title="{{ $product->is_active ? 'Desactivar' : 'Activar' }}"
                                                aria-label="{{ $product->is_active ? 'Desactivar' : 'Activar' }} {{ $product->name }}"
                                            >
                                                <x-action-icon name="toggle" />
                                            </button>
                                        </x-slot:trigger>
                                    </x-confirm-dialog>
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
