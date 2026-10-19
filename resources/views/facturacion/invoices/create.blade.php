<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Nueva Factura</h2>
            <a href="{{ route('facturacion.facturas.index') }}" class="brutal-button">Volver</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        @if($errors->any())
            <div class="brutal-card mb-6 !bg-danger !text-white">
                <p class="font-bold mb-2">Revisa los siguientes errores:</p>
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('facturacion.facturas.store') }}" x-data="invoiceForm()">
            @csrf

            <div class="brutal-card space-y-6">
                {{-- Cliente --}}
                <div>
                    <label class="brutal-label mb-1 block">Cliente</label>
                    <select name="customer_id" class="brutal-input">
                        <option value="">Consumidor Final</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                {{ $customer->display_name }} — {{ $customer->document_number }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Ítems --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="brutal-kicker text-xs">Ítems</h4>
                        <button type="button" class="brutal-button !py-1 !text-xs" @click="addItem()">
                            + Agregar ítem
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="border-[2px] border-ink p-3">
                                <div class="grid gap-3 sm:grid-cols-12">
                                    <div class="sm:col-span-5">
                                        <label class="brutal-label text-[10px] mb-1 block">Producto</label>
                                        <select
                                            :name="`items[${index}][product_id]`"
                                            x-model="item.product_id"
                                            @change="onProductChange(index)"
                                            class="brutal-input text-sm"
                                        >
                                            <option value="">(Ítem libre)</option>
                                            <template x-for="product in products" :key="product.id">
                                                <option :value="product.id" x-text="product.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-3">
                                        <label class="brutal-label text-[10px] mb-1 block">Descripción</label>
                                        <input
                                            type="text"
                                            :name="`items[${index}][description]`"
                                            x-model="item.description"
                                            class="brutal-input text-sm"
                                            required
                                        >
                                    </div>
                                    <div class="sm:col-span-1">
                                        <label class="brutal-label text-[10px] mb-1 block">Cant.</label>
                                        <input
                                            type="number"
                                            min="1"
                                            :name="`items[${index}][quantity]`"
                                            x-model.number="item.quantity"
                                            class="brutal-input text-sm"
                                            required
                                        >
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="brutal-label text-[10px] mb-1 block">Precio</label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            :name="`items[${index}][unit_price]`"
                                            x-model.number="item.unit_price"
                                            class="brutal-input text-sm"
                                            required
                                        >
                                    </div>
                                    <div class="sm:col-span-1">
                                        <label class="brutal-label text-[10px] mb-1 block">IVA %</label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            :name="`items[${index}][tax_rate]`"
                                            x-model.number="item.tax_rate"
                                            class="brutal-input text-sm"
                                        >
                                    </div>
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="font-mono text-sm text-muted" x-text="money(lineTotal(item))"></span>
                                    <button
                                        type="button"
                                        class="text-danger text-xs"
                                        @click="removeItem(index)"
                                    >
                                        Quitar
                                    </button>
                                </div>
                            </div>
                        </template>

                        <p x-show="items.length === 0" class="text-center py-6 text-muted">
                            Agrega al menos un ítem.
                        </p>
                    </div>
                </div>

                {{-- Totales --}}
                <div class="border-t-[2px] border-ink pt-4 flex justify-end">
                    <div class="w-full sm:w-64 space-y-2">
                        <div class="flex justify-between">
                            <span class="brutal-label text-xs">Subtotal</span>
                            <span class="font-mono" x-text="money(subtotal)"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="brutal-label text-xs">Impuestos</span>
                            <span class="font-mono" x-text="money(tax)"></span>
                        </div>
                        <div class="flex justify-between border-t-[2px] border-ink pt-2">
                            <span class="brutal-kicker text-sm">Total</span>
                            <span class="font-mono text-lg font-bold" x-text="money(total)"></span>
                        </div>
                    </div>
                </div>

                {{-- Pago (efectivo) --}}
                <div>
                    <h4 class="brutal-kicker mb-3 text-xs">Pago</h4>
                    <input type="hidden" name="payment_form_code" value="1">
                    <input type="hidden" name="payment_method_code" value="cash">
                    <input type="hidden" name="payments[0][payment_form_code]" value="1">
                    <input type="hidden" name="payments[0][payment_method_code]" value="cash">
                    <input type="hidden" name="payments[0][amount]" :value="total">
                    <input type="hidden" name="payments[0][cash_received]" :value="total">
                    <input type="hidden" name="payments[0][cash_change]" value="0">
                    <p class="text-sm text-muted">
                        Pago registrado en efectivo por un total de
                        <span class="font-mono font-bold text-ink" x-text="money(total)"></span>.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('facturacion.facturas.index') }}" class="brutal-button">Cancelar</a>
                <button type="submit" class="brutal-button !bg-primary !text-white" :disabled="items.length === 0">
                    Guardar Borrador
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            @php
                $productData = $products->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => (float) $p->price,
                    'tax_rate' => (float) $p->tax_rate,
                ])->values();
            @endphp
            const PRODUCTS = {!! json_encode($productData) !!};

            Alpine.data('invoiceForm', () => ({
                products: PRODUCTS,
                items: [{
                    product_id: '',
                    description: '',
                    quantity: 1,
                    unit_price: 0,
                    tax_rate: 0,
                }],

                addItem() {
                    this.items.push({
                        product_id: '',
                        description: '',
                        quantity: 1,
                        unit_price: 0,
                        tax_rate: 0,
                    });
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },

                onProductChange(index) {
                    const item = this.items[index];
                    const product = this.products.find(p => p.id === Number(item.product_id));
                    if (product) {
                        item.description = product.name;
                        item.unit_price = product.price;
                        item.tax_rate = product.tax_rate;
                    }
                },

                lineTotal(item) {
                    const base = (item.quantity || 0) * (item.unit_price || 0);
                    return base + (base * (item.tax_rate || 0) / 100);
                },

                get subtotal() {
                    return this.items.reduce((sum, item) => sum + ((item.quantity || 0) * (item.unit_price || 0)), 0);
                },

                get tax() {
                    return this.items.reduce((sum, item) => {
                        const base = (item.quantity || 0) * (item.unit_price || 0);
                        return sum + (base * (item.tax_rate || 0) / 100);
                    }, 0);
                },

                get total() {
                    return this.subtotal + this.tax;
                },

                money(value) {
                    return '$' + Number(value || 0).toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },
            }));
        });
    </script>
</x-app-layout>
