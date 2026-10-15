<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>POS - {{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,700|inter-tight:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .pos-layout {
                display: grid;
                grid-template-columns: 1fr 400px;
                height: 100vh;
                overflow: hidden;
            }

            .pos-catalog {
                overflow-y: auto;
                padding: 1.5rem;
            }

            .pos-cart {
                border-left: 3px solid var(--color-ink);
                background-color: var(--color-surface);
                display: flex;
                flex-direction: column;
                height: 100vh;
            }

            .pos-cart-items {
                flex: 1;
                overflow-y: auto;
                padding: 1rem;
            }

            .pos-cart-footer {
                border-top: 3px solid var(--color-ink);
                padding: 1rem;
            }

            @media (max-width: 1024px) {
                .pos-layout {
                    grid-template-columns: 1fr;
                    grid-template-rows: 1fr 300px;
                }

                .pos-cart {
                    border-left: none;
                    border-top: 3px solid var(--color-ink);
                    height: auto;
                }
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="pos-layout bg-bg text-ink" x-data="posApp()">
            {{-- Panel de catálogo --}}
            <div class="pos-catalog">
                {{-- Barra de búsqueda --}}
                <div class="brutal-card mb-4">
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <input
                                type="text"
                                x-model="productTerm"
                                @input.debounce.300ms="searchProducts()"
                                placeholder="Buscar producto por nombre o SKU..."
                                class="brutal-input"
                                autofocus
                            >
                        </div>
                        <div class="w-48">
                            <input
                                type="text"
                                x-model="barcode"
                                @keydown.enter.prevent="searchByBarcode()"
                                placeholder="Escanear código..."
                                class="brutal-input"
                            >
                        </div>
                    </div>
                </div>

                {{-- Grid de productos --}}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <template x-for="product in products" :key="product.id">
                        <button
                            type="button"
                            @click="addItem(product)"
                            class="brutal-card text-left hover:-translate-x-0.5 hover:-translate-y-0.5 transition-transform"
                        >
                            <p class="brutal-kicker text-[10px] text-muted" x-text="product.sku || '—'"></p>
                            <p class="mt-1 font-semibold leading-tight" x-text="product.name"></p>
                            <p class="mt-2 font-mono font-bold" x-text="money(product.price)"></p>
                        </button>
                    </template>

                    <div
                        x-show="products.length === 0"
                        class="brutal-card text-center py-8 text-muted col-span-full"
                    >
                        <span x-show="!loading">Escribe para buscar productos...</span>
                        <span x-show="loading">Buscando...</span>
                        <span x-show="!loading && productTerm.length >= 2 && products.length === 0">
                            Sin resultados para "<span x-text="productTerm"></span>".
                        </span>
                    </div>
                </div>
            </div>

            {{-- Panel del carrito --}}
            <div class="pos-cart">
                {{-- Header del carrito --}}
                <div class="p-4 border-b-[3px] border-ink">
                    <div class="flex items-center justify-between">
                        <h2 class="brutal-kicker text-lg">Carrito</h2>
                        <a href="{{ route('facturacion.facturas.index') }}" class="brutal-label text-[10px] underline">
                            Ver facturas
                        </a>
                    </div>
                    <div class="mt-2 relative">
                        <input
                            type="text"
                            x-model="customerTerm"
                            @input.debounce.300ms="searchCustomers()"
                            placeholder="Buscar cliente (opcional)..."
                            class="brutal-input text-sm"
                        >
                        <div
                            x-show="customerResults.length > 0"
                            class="absolute z-10 w-full border-[2px] border-t-0 border-ink bg-surface"
                        >
                            <template x-for="customer in customerResults" :key="customer.id">
                                <button
                                    type="button"
                                    class="block w-full px-3 py-2 text-left text-sm hover:bg-bg"
                                    @click="selectCustomer(customer)"
                                >
                                    <span class="font-semibold" x-text="customer.display_name"></span>
                                    <span class="text-muted text-xs" x-text="' · ' + (customer.document_number || '')"></span>
                                </button>
                            </template>
                        </div>
                        <div x-show="customer" class="mt-2 flex items-center">
                            <span class="brutal-badge bg-success text-white" x-text="customer?.display_name"></span>
                            <button type="button" @click="clearCustomer()" class="text-danger text-xs ml-2">✕</button>
                        </div>
                    </div>
                </div>

                {{-- Items del carrito --}}
                <div class="pos-cart-items">
                    <template x-for="item in items" :key="item.product_id">
                        <div class="border-b-[2px] border-ink py-3">
                            <div class="flex items-start justify-between gap-2">
                                <p class="font-semibold text-sm leading-tight" x-text="item.name"></p>
                                <button type="button" @click="removeItem(item.product_id)" class="text-danger text-xs">✕</button>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <button type="button" class="brutal-button !px-2 !py-0.5" @click="changeQty(item, -1)">−</button>
                                    <span class="font-mono w-8 text-center" x-text="item.quantity"></span>
                                    <button type="button" class="brutal-button !px-2 !py-0.5" @click="changeQty(item, 1)">+</button>
                                </div>
                                <span class="font-mono text-sm" x-text="money(item.price * item.quantity)"></span>
                            </div>
                        </div>
                    </template>

                    <div x-show="items.length === 0" class="text-center py-8 text-muted">
                        El carrito está vacío
                    </div>
                </div>

                {{-- Footer del carrito --}}
                <div class="pos-cart-footer">
                    {{-- Resumen --}}
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="brutal-label text-[10px]">Subtotal</span>
                            <span class="font-mono" x-text="money(subtotal)"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="brutal-label text-[10px]">Impuestos</span>
                            <span class="font-mono" x-text="money(tax)"></span>
                        </div>
                        <div class="flex justify-between border-t-[2px] border-ink pt-2">
                            <span class="brutal-kicker text-sm">Total</span>
                            <span class="font-mono text-xl font-bold" x-text="money(total)"></span>
                        </div>
                    </div>

                    <p x-show="error" class="brutal-alert mb-3 bg-danger text-white text-xs" x-text="error"></p>
                    <p x-show="success" class="brutal-alert mb-3 bg-success text-white text-xs" x-text="success"></p>

                    {{-- Acciones --}}
                    <div class="flex gap-2">
                        <button type="button" @click="clearCart()" class="brutal-button flex-1">
                            Limpiar
                        </button>
                        <button
                            type="button"
                            @click="checkout()"
                            :disabled="items.length === 0 || processing"
                            class="brutal-button flex-1 !bg-success !text-white disabled:opacity-50"
                        >
                            <span x-show="!processing">Cobrar</span>
                            <span x-show="processing">Procesando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('posApp', () => ({
                    productTerm: '',
                    barcode: '',
                    products: [],
                    loading: false,
                    customerTerm: '',
                    customerResults: [],
                    customer: null,
                    items: [],
                    processing: false,
                    error: '',
                    success: '',

                    get subtotal() {
                        return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                    },

                    get tax() {
                        return this.items.reduce((sum, item) => sum + ((item.price * item.quantity * item.tax_rate) / 100), 0);
                    },

                    get total() {
                        return this.subtotal + this.tax;
                    },

                    money(value) {
                        return '$' + Number(value || 0).toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    },

                    async searchProducts() {
                        this.error = '';
                        if (this.productTerm.length < 2) {
                            this.products = [];
                            return;
                        }
                        this.loading = true;
                        try {
                            const res = await fetch(`{{ route('facturacion.pos.productos') }}?term=${encodeURIComponent(this.productTerm)}`, {
                                headers: { 'Accept': 'application/json' },
                            });
                            this.products = res.ok ? await res.json() : [];
                        } catch (e) {
                            this.error = 'No se pudo buscar productos.';
                        } finally {
                            this.loading = false;
                        }
                    },

                    async searchByBarcode() {
                        const code = this.barcode.trim();
                        if (!code) return;
                        this.productTerm = code;
                        await this.searchProducts();
                        if (this.products.length === 1) {
                            this.addItem(this.products[0]);
                        }
                        this.barcode = '';
                    },

                    async searchCustomers() {
                        if (this.customerTerm.length < 2) {
                            this.customerResults = [];
                            return;
                        }
                        try {
                            const res = await fetch(`{{ route('facturacion.pos.clientes') }}?term=${encodeURIComponent(this.customerTerm)}`, {
                                headers: { 'Accept': 'application/json' },
                            });
                            this.customerResults = res.ok ? await res.json() : [];
                        } catch (e) {
                            this.customerResults = [];
                        }
                    },

                    selectCustomer(customer) {
                        this.customer = customer;
                        this.customerResults = [];
                        this.customerTerm = customer.display_name;
                    },

                    clearCustomer() {
                        this.customer = null;
                        this.customerTerm = '';
                    },

                    addItem(product) {
                        const existing = this.items.find(i => i.product_id === product.id);
                        if (existing) {
                            existing.quantity++;
                        } else {
                            this.items.push({
                                product_id: product.id,
                                name: product.name,
                                price: Number(product.price),
                                tax_rate: Number(product.tax_rate),
                                unit_measure_code: product.unit_measure_code || '94',
                                quantity: 1,
                            });
                        }
                    },

                    removeItem(productId) {
                        this.items = this.items.filter(i => i.product_id !== productId);
                    },

                    changeQty(item, delta) {
                        item.quantity = Math.max(1, item.quantity + delta);
                    },

                    clearCart() {
                        this.items = [];
                        this.customer = null;
                        this.customerTerm = '';
                        this.error = '';
                        this.success = '';
                    },

                    async checkout() {
                        if (this.items.length === 0) return;
                        this.processing = true;
                        this.error = '';
                        this.success = '';

                        const payload = {
                            customer_id: this.customer?.id ?? null,
                            payment_form_code: '1',
                            payment_method_code: 'cash',
                            items: this.items.map(i => ({
                                product_id: i.product_id,
                                description: i.name,
                                quantity: i.quantity,
                                unit_price: i.price,
                                tax_rate: i.tax_rate,
                                unit_measure_code: i.unit_measure_code,
                            })),
                            payments: [
                                {
                                    payment_form_code: '1',
                                    payment_method_code: 'cash',
                                    amount: this.total,
                                },
                            ],
                        };

                        try {
                            const res = await fetch(`{{ route('facturacion.pos.checkout') }}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify(payload),
                            });

                            const data = await res.json();

                            if (!res.ok) {
                                this.error = data.message || 'No se pudo procesar la venta.';
                                return;
                            }

                            this.success = `Factura ${data.invoice.number} emitida.`;
                            this.items = [];

                            if (data.invoice.url) {
                                this.success += ' Redirigiendo...';
                                setTimeout(() => window.location.href = data.invoice.url, 900);
                            }
                        } catch (e) {
                            this.error = 'Error de red al procesar la venta.';
                        } finally {
                            this.processing = false;
                        }
                    },
                }));
            });
        </script>
    </body>
</html>
