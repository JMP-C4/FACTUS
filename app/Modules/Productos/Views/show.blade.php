<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Detalle del Producto</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('productos.edit', $product) }}" class="brutal-button">
                    Editar
                </a>
                <a href="{{ route('productos.index') }}" class="brutal-button">
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="brutal-card space-y-6">
            {{-- Encabezado --}}
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-xl font-bold">{{ $product->name }}</h3>
                    @if($product->description)
                        <p class="mt-1 text-sm text-muted">{{ $product->description }}</p>
                    @endif
                </div>
                @if($product->is_active)
                    <span class="brutal-badge bg-success text-white">Activo</span>
                @else
                    <span class="brutal-badge bg-muted text-white">Inactivo</span>
                @endif
            </div>

            {{-- Datos básicos --}}
            <div>
                <h4 class="brutal-kicker mb-3 text-xs">Identificadores</h4>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <span class="brutal-label text-[10px] text-muted">SKU</span>
                        <p class="font-mono font-bold">{{ $product->sku }}</p>
                    </div>
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Código de Barras</span>
                        <p class="font-mono font-bold">{{ $product->barcode ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Datos comerciales --}}
            <div>
                <h4 class="brutal-kicker mb-3 text-xs">Datos Comerciales</h4>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Precio</span>
                        <p class="font-mono text-lg font-bold">${{ number_format($product->price, 2, ',', '.') }}</p>
                    </div>
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Impuesto</span>
                        <p class="font-mono text-lg font-bold">{{ $product->tax_rate }}%</p>
                        @if($product->tax)
                            <p class="text-[10px] uppercase text-muted">{{ $product->tax->name }}</p>
                        @endif
                    </div>
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Stock Mínimo</span>
                        <p class="font-mono text-lg font-bold">{{ $product->minimum_stock }}</p>
                    </div>
                </div>
            </div>

            {{-- Clasificación --}}
            <div>
                <h4 class="brutal-kicker mb-3 text-xs">Clasificación</h4>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Categoría</span>
                        <p class="font-bold">{{ $product->category?->name ?? 'Sin categoría' }}</p>
                    </div>
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Marca</span>
                        <p class="font-bold">{{ $product->brand?->name ?? 'Sin marca' }}</p>
                    </div>
                </div>
            </div>

            {{-- Datos fiscales --}}
            <div>
                <h4 class="brutal-kicker mb-3 text-xs">Datos Fiscales</h4>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Unidad de Medida</span>
                        <p class="font-mono font-bold">{{ $product->unit_measure_code }}</p>
                    </div>
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Código Estándar</span>
                        <p class="font-mono font-bold">{{ $product->standard_code ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Metadatos --}}
            <div class="pt-4 border-t-[2px] border-ink">
                <div class="grid gap-4 sm:grid-cols-2 text-xs text-muted">
                    <div>
                        <span class="brutal-label text-[10px]">Creado</span>
                        <p>{{ $product->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <span class="brutal-label text-[10px]">Actualizado</span>
                        <p>{{ $product->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
