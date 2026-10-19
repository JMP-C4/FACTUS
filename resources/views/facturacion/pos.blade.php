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
            [x-cloak] { display: none !important; }

            .pos-layout {
                display: grid;
                grid-template-columns: 1fr minmax(360px, 420px);
                height: 100vh;
                overflow: hidden;
            }

            .pos-catalog {
                display: flex;
                flex-direction: column;
                overflow: hidden;
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

            .pos-shortcuts {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.375rem 0.75rem;
                border: 2px solid var(--color-ink);
                background-color: var(--color-surface);
                padding: 0.4rem 0.6rem;
                position: sticky;
                top: 0;
                z-index: 20;
                margin-bottom: 1rem;
            }

            .pos-shortcut {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                font-size: 0.65rem;
                font-weight: 600;
                color: var(--color-ink);
                background: none;
                border: none;
                padding: 0;
                cursor: pointer;
            }

            .pos-kbd {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 1.5rem;
                padding: 0.1rem 0.35rem;
                border: 2px solid var(--color-ink);
                background-color: var(--color-bg);
                font-family: var(--font-body);
                font-size: 0.62rem;
                font-weight: 800;
                box-shadow: 2px 2px 0 0 var(--color-ink);
            }

            .pos-shortcut:hover .pos-kbd,
            .pos-shortcut:focus-visible .pos-kbd {
                background-color: var(--color-accent);
            }

            .pos-payment-preview {
                border: 2px solid var(--color-ink);
                background-color: var(--color-bg);
            }

            .pos-results {
                flex: 1 1 auto;
                min-height: 0;
                overflow-y: auto;
                border: 2px solid var(--color-ink);
                background-color: var(--color-surface);
            }

            .pos-result-row {
                display: grid;
                grid-template-columns: auto minmax(0, 1fr) auto auto;
                align-items: center;
                gap: 0.75rem;
                width: 100%;
                padding: 0.55rem 0.75rem;
                text-align: left;
                background: transparent;
                border: none;
                border-bottom: 2px solid var(--color-ink);
                cursor: pointer;
            }

            .pos-result-row:last-child {
                border-bottom: none;
            }

            .pos-result-row:hover,
            .pos-result-row:focus-visible {
                background-color: var(--color-accent);
                outline: none;
            }

            .pos-result-sku {
                font-family: var(--font-body);
                font-size: 0.62rem;
                font-weight: 800;
                letter-spacing: 0.06em;
                text-transform: uppercase;
                border: 2px solid var(--color-ink);
                padding: 0.1rem 0.35rem;
                background-color: var(--color-bg);
                white-space: nowrap;
            }

            .pos-result-name {
                display: block;
                font-weight: 600;
                line-height: 1.15;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .pos-result-meta {
                display: block;
                font-size: 0.65rem;
                color: var(--color-text-soft);
            }

            .pos-result-price {
                font-family: var(--font-body);
                font-weight: 700;
                white-space: nowrap;
            }

            .pos-result-add {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 1.75rem;
                height: 1.75rem;
                border: 2px solid var(--color-ink);
                background-color: var(--color-bg);
                font-weight: 800;
                box-shadow: 2px 2px 0 0 var(--color-ink);
            }

            .pos-cart-item {
                border-bottom: 2px solid var(--color-ink);
                padding: 0.6rem 0;
                cursor: pointer;
            }

            .pos-cart-item[data-selected="true"] {
                background-color: var(--color-accent);
                box-shadow: inset 4px 0 0 0 var(--color-ink);
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
                {{-- Barra de atajos de teclado (estándar POS) --}}
                <div class="pos-shortcuts" role="region" aria-label="Atajos de teclado del punto de venta">
                    <span class="brutal-kicker text-[10px] text-muted">Atajos</span>
                    <template x-for="shortcut in shortcuts" :key="'bar-' + shortcut.keys">
                        <button
                            type="button"
                            class="pos-shortcut"
                            :title="shortcut.description"
                            :aria-label="shortcut.description"
                            @click="runShortcut(shortcut.action)"
                        >
                            <kbd class="pos-kbd" x-text="shortcut.keys"></kbd>
                            <span x-text="shortcut.label"></span>
                        </button>
                    </template>
                </div>

                {{-- Barra de búsqueda --}}
                <div class="brutal-card mb-4">
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="brutal-label mb-1 block text-[10px] text-muted">Producto (F2)</label>
                            <input
                                type="text"
                                x-model="productTerm"
                                x-ref="productSearch"
                                @input.debounce.300ms="searchProducts()"
                                @keydown.enter.prevent="addTopResult()"
                                placeholder="Buscar producto por nombre o SKU..."
                                class="brutal-input"
                                autofocus
                            >
                        </div>
                        <div class="w-56">
                            <label class="brutal-label mb-1 block text-[10px] text-muted">Código (F4)</label>
                            <input
                                type="text"
                                x-model="barcode"
                                x-ref="barcodeInput"
                                @keydown.enter.prevent="searchByBarcode()"
                                placeholder="Escanear código..."
                                class="brutal-input"
                            >
                        </div>
                    </div>

                    <div x-show="barcodeNotFound" class="brutal-alert mt-3 bg-accent text-ink text-xs items-center justify-between gap-2">
                        <span>¿Crear un producto con el código <span class="font-mono font-bold" x-text="barcodeNotFound"></span>?</span>
                        <a :href="createProductUrl" class="brutal-button !px-3 !py-1 text-[10px] shrink-0" target="_blank" rel="noopener">
                            Crear producto
                        </a>
                    </div>
                </div>

                {{-- Lista de resultados --}}
                <div class="pos-results">
                    <template x-for="product in products" :key="product.id">
                        <button
                            type="button"
                            class="pos-result-row"
                            @click="addItem(product)"
                        >
                            <span class="pos-result-sku" x-text="product.sku || '—'"></span>
                            <span class="min-w-0">
                                <span class="pos-result-name" x-text="product.name"></span>
                                <span class="pos-result-meta" x-text="product.barcode || 'Sin código de barras'"></span>
                            </span>
                            <span class="pos-result-price" x-text="money(product.price)"></span>
                            <span class="pos-result-add" aria-hidden="true">+</span>
                        </button>
                    </template>

                    <div
                        x-show="products.length === 0"
                        class="text-center py-10 px-4 text-muted"
                    >
                        <span x-show="!loading && productTerm.length < 2">Escribe para buscar productos...</span>
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
                        <h2 class="brutal-kicker text-lg">
                            Carrito
                            <span class="ml-1 font-mono text-xs text-muted" x-text="'(' + items.length + ')'"></span>
                        </h2>
                        <a href="{{ route('facturacion.facturas.index') }}" class="brutal-label text-[10px] underline">
                            Ver facturas
                        </a>
                    </div>
                    <div class="mt-2 relative">
                        <input
                            type="text"
                            x-model="customerTerm"
                            x-ref="customerSearch"
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
                        <div
                            class="pos-cart-item"
                            :data-selected="selectedItemId === item.product_id"
                            @click="selectItem(item)"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <p class="font-semibold text-sm leading-tight" x-text="item.name"></p>
                                <button
                                    type="button"
                                    class="brutal-label text-[10px] text-danger underline shrink-0"
                                    title="Retirar (requiere autorización de un superior)"
                                    aria-label="Retirar producto"
                                    @click.stop="openRemoval(item.product_id)"
                                >
                                    Retirar
                                </button>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <button type="button" class="brutal-button !px-2 !py-0.5" @click.stop="changeQty(item, -1)">−</button>
                                    <span class="font-mono w-8 text-center" x-text="item.quantity"></span>
                                    <button type="button" class="brutal-button !px-2 !py-0.5" @click.stop="changeQty(item, 1)">+</button>
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

                    <p x-show="success" class="brutal-alert mb-3 bg-success text-white text-xs" x-text="success"></p>

                    {{-- Acciones --}}
                    <div class="flex gap-2">
                        @if(auth()->user()?->hasRole('admin') || auth()->user()?->isSuperAdmin())
                            <button type="button" @click="clearCart()" class="brutal-button" title="Limpiar carrito (F8)">
                                Limpiar
                            </button>
                        @endif
                        <button
                            type="button"
                            @click="openPayment()"
                            :disabled="items.length === 0 || processing"
                            class="brutal-button flex-1 !bg-success !text-white disabled:opacity-50"
                        >
                            Pagar · <span x-text="money(total)"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Modal de autorización para retirar productos --}}
            @include('facturacion::partials.removal-modal')

            {{-- Modal de pago --}}
            @include('facturacion::partials.payment-modal')

            {{-- Modal de ayuda de atajos de teclado (F1) --}}
            <div
                x-cloak
                x-show="shortcutsHelp"
                x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                role="dialog"
                aria-modal="true"
                aria-label="Atajos de teclado del punto de venta"
            >
                <div class="w-full max-w-md border-[3px] border-ink bg-surface p-6 shadow-[8px_8px_0_var(--color-primary)]">
                    <h3 class="brutal-kicker text-lg">Atajos de teclado</h3>
                    <p class="mt-1 text-sm text-muted">
                        Usa el teclado para operar la caja sin soltar el lector de códigos.
                    </p>

                    <table class="mt-4 w-full text-sm">
                        <tbody>
                            <template x-for="shortcut in shortcuts" :key="'help-' + shortcut.keys">
                                <tr class="border-b-[2px] border-ink/20">
                                    <td class="py-1 pr-3"><kbd class="pos-kbd" x-text="shortcut.keys"></kbd></td>
                                    <td class="py-1" x-text="shortcut.description"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <div class="mt-5 flex justify-end">
                        <button type="button" class="brutal-button" @click="shortcutsHelp = false">Cerrar</button>
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
                    splitMode: false,
                    cashReceived: 0,
                    payments: [],
                    paymentModal: false,
                    selectedItemId: null,
                    canClearCart: {{ auth()->user()?->hasRole('admin') || auth()->user()?->isSuperAdmin() ? 'true' : 'false' }},
                    removalModal: false,
                    removalTarget: null,
                    removalMethod: 'code',
                    removalCode: '',
                    removalEmail: '',
                    removalPassword: '',
                    removalError: '',
                    removalLoading: false,
                    shortcutsHelp: false,
                    barcodeNotFound: '',
                    keydownHandler: null,
                    methodLabels: {
                        cash: 'Efectivo',
                        debit_card: 'Tarjeta débito',
                        credit_card: 'Tarjeta crédito',
                        transfer: 'Transferencia',
                    },
                    shortcuts: [
                        { keys: 'F1', label: 'Ayuda', description: 'Mostrar u ocultar esta ayuda de atajos', action: 'toggleShortcutsHelp' },
                        { keys: 'F2', label: 'Producto', description: 'Buscar producto por nombre o SKU', action: 'focusProductSearch' },
                        { keys: 'F3', label: 'Cliente', description: 'Buscar cliente', action: 'focusCustomerSearch' },
                        { keys: 'F4', label: 'Escanear', description: 'Enfocar el lector de código de barras', action: 'focusBarcode' },
                        { keys: 'F6', label: 'Dividir', description: 'Alternar entre pago único y pago dividido', action: 'toggleSplit' },
                        { keys: 'F7', label: 'Añadir pago', description: 'Añadir una parte de pago', action: 'addPayment' },
                        @if(auth()->user()?->hasRole('admin') || auth()->user()?->isSuperAdmin())
                        { keys: 'F8', label: 'Limpiar', description: 'Limpiar la venta actual', action: 'clearCart' },
                        @endif
                        { keys: 'Supr', label: 'Retirar', description: 'Retirar el producto seleccionado (requiere autorización)', action: 'removeSelected' },
                        { keys: 'F9', label: 'Pagar', description: 'Abrir el pago para cobrar la venta', action: 'openPayment' },
                        { keys: 'Esc', label: 'Cerrar', description: 'Cerrar el diálogo abierto', action: 'closeOverlays' },
                        { keys: 'Ctrl+↵', label: 'Confirmar', description: 'Confirmar la venta desde cualquier campo', action: 'checkout' },
                    ],

                    init() {
                        this.$watch('total', (value) => {
                            if (! this.splitMode) {
                                if (this.cashReceived < value) {
                                    this.cashReceived = value;
                                }
                                return;
                            }

                            this.rebalancePayments();
                        });

                        this.keydownHandler = (event) => this.handleShortcut(event);
                        window.addEventListener('keydown', this.keydownHandler);
                    },

                    destroy() {
                        if (this.keydownHandler !== null) {
                            window.removeEventListener('keydown', this.keydownHandler);
                        }
                    },

                    get subtotal() {
                        return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                    },

                    get tax() {
                        return this.items.reduce((sum, item) => sum + ((item.price * item.quantity * item.tax_rate) / 100), 0);
                    },

                    get total() {
                        return this.subtotal + this.tax;
                    },

                    get change() {
                        return (Number(this.cashReceived) || 0) - this.total;
                    },

                    get paidTotal() {
                        if (! this.splitMode) {
                            return this.total;
                        }

                        return this.payments.reduce((sum, payment) => sum + (Number(payment.amount) || 0), 0);
                    },

                    get remaining() {
                        return Math.max(this.total - this.paidTotal, 0);
                    },

                    get paymentsValid() {
                        if (! this.splitMode) {
                            return this.change >= -0.001;
                        }

                        return Math.abs(this.paidTotal - this.total) <= 0.01;
                    },

                    get paymentSummary() {
                        if (! this.splitMode) {
                            return [{ key: 'unique', label: 'Efectivo', amount: this.total }];
                        }

                        return this.payments.map((payment, index) => ({
                            key: `${payment.method}-${index}`,
                            label: `${this.methodLabel(payment.method)} · Parte ${index + 1}`,
                            amount: Number(payment.amount) || 0,
                        }));
                    },

                    get createProductUrl() {
                        const base = @json(route('productos.create'));

                        return this.barcodeNotFound
                            ? `${base}?barcode=${encodeURIComponent(this.barcodeNotFound)}`
                            : base;
                    },

                    get removalTargetName() {
                        const item = this.items.find(i => i.product_id === this.removalTarget);

                        return item ? item.name : '';
                    },

                    methodLabel(code) {
                        return this.methodLabels[code] ?? code;
                    },

                    maxForPayment(payment) {
                        const others = this.paidTotal - (Number(payment.amount) || 0);
                        return Math.max(this.total - others, 0);
                    },

                    clampPayment(payment) {
                        const max = this.maxForPayment(payment);
                        let amount = Number(payment.amount) || 0;

                        if (amount < 0) {
                            amount = 0;
                        }
                        if (amount > max) {
                            amount = max;
                        }

                        payment.amount = amount;
                        this.syncCashReceived(payment);
                    },

                    syncCashReceived(payment) {
                        if (payment.method === 'cash' && (Number(payment.cash_received) || 0) < (Number(payment.amount) || 0)) {
                            payment.cash_received = Number(payment.amount) || 0;
                        }
                    },

                    /**
                     * Ajusta el monto de una parte del pago dividido.
                     *
                     * Con dos partes, la edición de una recalcula la otra para
                     * que ambas siempre sumen el total. Con más de dos, cada
                     * parte se limita al saldo disponible.
                     */
                    onSplitAmount(changed) {
                        if (this.payments.length === 2) {
                            let amount = Number(changed.amount) || 0;
                            amount = Math.min(Math.max(amount, 0), this.total);
                            changed.amount = amount;
                            this.syncCashReceived(changed);

                            const other = this.payments.find((payment) => payment !== changed);

                            if (other) {
                                other.amount = Math.max(this.total - amount, 0);
                                this.syncCashReceived(other);
                            }

                            return;
                        }

                        this.clampPayment(changed);
                    },

                    /**
                     * Ajusta las partes existentes cuando cambia el total.
                     */
                    rebalancePayments() {
                        if (this.payments.length === 0) {
                            return;
                        }

                        if (this.payments.length === 1) {
                            this.payments[0].amount = this.total;
                            this.syncCashReceived(this.payments[0]);
                            return;
                        }

                        const others = this.payments
                            .slice(0, -1)
                            .reduce((sum, payment) => sum + (Number(payment.amount) || 0), 0);

                        const last = this.payments[this.payments.length - 1];
                        last.amount = Math.max(this.total - others, 0);
                        this.syncCashReceived(last);
                    },

                    money(value) {
                        return '$' + Number(value || 0).toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    },

                    async searchProducts() {
                        this.error = '';
                        this.barcodeNotFound = '';
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

                        this.error = '';
                        this.barcodeNotFound = '';
                        this.productTerm = code;
                        await this.searchProducts();

                        if (this.products.length === 1) {
                            this.addItem(this.products[0]);
                        } else if (this.products.length === 0) {
                            this.barcodeNotFound = code;
                        }

                        this.barcode = '';
                    },

                    addTopResult() {
                        if (this.products.length > 0) {
                            this.addItem(this.products[0]);
                        }
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

                        this.selectedItemId = product.id;
                    },

                    selectItem(item) {
                        this.selectedItemId = item.product_id;
                    },

                    /**
                     * Retira el producto seleccionado (o el último) abriendo el
                     * modal de autorización de un superior.
                     */
                    removeSelected() {
                        if (this.items.length === 0) {
                            return;
                        }

                        const selected = this.items.find(i => i.product_id === this.selectedItemId);

                        if (selected) {
                            this.openRemoval(selected.product_id);
                            return;
                        }

                        this.openRemoval(this.items[this.items.length - 1].product_id);
                    },

                    openPayment() {
                        if (this.items.length === 0) {
                            return;
                        }

                        this.error = '';
                        this.success = '';
                        this.paymentModal = true;
                    },

                    closePayment() {
                        this.paymentModal = false;
                        this.error = '';
                    },

                    openRemoval(productId) {
                        this.removalTarget = productId;
                        this.removalMethod = 'code';
                        this.removalCode = '';
                        this.removalEmail = '';
                        this.removalPassword = '';
                        this.removalError = '';
                        this.removalModal = true;
                    },

                    closeRemoval() {
                        this.removalModal = false;
                        this.removalTarget = null;
                        this.removalError = '';
                    },

                    async confirmRemoval() {
                        if (this.removalTarget === null) return;

                        this.removalLoading = true;
                        this.removalError = '';

                        const payload = this.removalMethod === 'code'
                            ? { code: this.removalCode }
                            : { email: this.removalEmail, password: this.removalPassword };

                        try {
                            const res = await fetch(`{{ route('facturacion.pos.autorizarRetiro') }}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify(payload),
                            });

                            const data = await res.json();

                            if (! res.ok || ! data.authorized) {
                                this.removalError = data.message || 'Autorización denegada.';
                                return;
                            }

                            this.items = this.items.filter(i => i.product_id !== this.removalTarget);
                            if (this.selectedItemId === this.removalTarget) {
                                this.selectedItemId = null;
                            }
                            this.closeRemoval();
                        } catch (e) {
                            this.removalError = 'Error de red al verificar la autorización.';
                        } finally {
                            this.removalLoading = false;
                        }
                    },

                    changeQty(item, delta) {
                        item.quantity = Math.max(1, item.quantity + delta);
                    },

                    clearCart() {
                        this.items = [];
                        this.selectedItemId = null;
                        this.customer = null;
                        this.customerTerm = '';
                        this.error = '';
                        this.success = '';
                        this.barcodeNotFound = '';
                        this.cashReceived = 0;
                        this.paymentModal = false;
                        this.resetPayments();
                    },

                    newPayment() {
                        return {
                            method: 'cash',
                            amount: 0,
                            cash_received: 0,
                            card_last_four: '',
                            card_approval_code: '',
                            reference_number: '',
                            bank_entity: '',
                        };
                    },

                    resetPayments() {
                        if (! this.splitMode) {
                            this.payments = [];
                            return;
                        }

                        this.payments = [
                            { ...this.newPayment(), method: 'cash', amount: this.total, cash_received: this.total },
                            { ...this.newPayment(), method: 'debit_card', amount: 0 },
                        ];
                    },

                    toggleSplit() {
                        this.splitMode = ! this.splitMode;
                        this.error = '';

                        if (! this.splitMode) {
                            this.payments = [];
                            return;
                        }

                        if (this.payments.length === 0) {
                            this.resetPayments();
                        }
                    },

                    addPayment() {
                        if (! this.splitMode) {
                            return;
                        }

                        const remaining = Math.max(this.total - this.paidTotal, 0);
                        this.payments.push({ ...this.newPayment(), method: 'cash', amount: remaining, cash_received: remaining });
                    },

                    removePayment(index) {
                        this.payments.splice(index, 1);

                        if (this.payments.length === 1) {
                            this.payments[0].amount = this.total;
                            this.syncCashReceived(this.payments[0]);
                        } else {
                            this.rebalancePayments();
                        }
                    },

                    // =========================================================================
                    // Atajos de teclado (estándar POS)
                    // =========================================================================

                    runShortcut(action) {
                        if (typeof this[action] === 'function') {
                            this[action]();
                        }
                    },

                    toggleShortcutsHelp() {
                        this.shortcutsHelp = ! this.shortcutsHelp;
                    },

                    focusProductSearch() {
                        this.$refs.productSearch?.focus();
                    },

                    focusCustomerSearch() {
                        this.$refs.customerSearch?.focus();
                    },

                    focusBarcode() {
                        this.$refs.barcodeInput?.focus();
                    },

                    closeOverlays() {
                        if (this.removalModal) {
                            this.closeRemoval();
                            return;
                        }

                        if (this.paymentModal && ! this.processing) {
                            this.closePayment();
                            return;
                        }

                        this.shortcutsHelp = false;
                    },

                    handleShortcut(event) {
                        if (event.key === 'Escape') {
                            this.closeOverlays();
                            return;
                        }

                        if (event.key === 'Enter' && (event.ctrlKey || event.metaKey)) {
                            if (this.items.length > 0 && ! this.processing) {
                                event.preventDefault();
                                this.checkout();
                            }
                            return;
                        }

                        if (this.removalModal || this.shortcutsHelp) {
                            return;
                        }

                        if (this.paymentModal) {
                            const paymentHandlers = {
                                F6: () => this.toggleSplit(),
                                F7: () => this.addPayment(),
                                F9: () => this.checkout(),
                            };

                            const paymentHandler = paymentHandlers[event.key];

                            if (paymentHandler) {
                                event.preventDefault();
                                paymentHandler();
                            }

                            return;
                        }

                        const handlers = {
                            F1: () => this.toggleShortcutsHelp(),
                            F2: () => this.focusProductSearch(),
                            F3: () => this.focusCustomerSearch(),
                            F4: () => this.focusBarcode(),
                            F6: () => this.toggleSplit(),
                            F7: () => this.addPayment(),
                            F8: () => { if (this.canClearCart) { this.clearCart(); } },
                            Supr: () => this.removeSelected(),
                            Delete: () => this.removeSelected(),
                            F9: () => this.openPayment(),
                        };

                        const handler = handlers[event.key];

                        if (handler) {
                            event.preventDefault();
                            handler();
                        }
                    },

                    buildPayments() {
                        if (! this.splitMode) {
                            return [{
                                payment_form_code: '1',
                                payment_method_code: 'cash',
                                amount: this.total,
                                cash_received: Number(this.cashReceived) || 0,
                                cash_change: Math.max(this.change, 0),
                            }];
                        }

                        return this.payments
                            .filter((payment) => (Number(payment.amount) || 0) > 0)
                            .map((payment) => {
                                const base = {
                                    payment_method_code: payment.method,
                                    amount: Number(payment.amount) || 0,
                                };

                                if (payment.method === 'cash') {
                                    base.cash_received = Number(payment.cash_received) || 0;
                                    base.cash_change = Math.max(base.cash_received - base.amount, 0);
                                }

                                if (payment.method === 'debit_card' || payment.method === 'credit_card') {
                                    base.card_last_four = payment.card_last_four;
                                    base.card_approval_code = payment.card_approval_code;
                                }

                                if (payment.method === 'transfer') {
                                    base.reference_number = payment.reference_number;
                                    base.bank_entity = payment.bank_entity;
                                }

                                return base;
                            });
                    },

                    validatePayments() {
                        if (! this.splitMode) {
                            if (this.change < -0.001) {
                                return 'El monto recibido es menor al total.';
                            }

                            return null;
                        }

                        if (Math.abs(this.paidTotal - this.total) > 0.01) {
                            return 'La suma de los pagos debe coincidir con el total.';
                        }

                        return null;
                    },

                    async checkout() {
                        if (this.items.length === 0) return;

                        const paymentError = this.validatePayments();
                        if (paymentError) {
                            this.error = paymentError;
                            return;
                        }

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
                            payments: this.buildPayments(),
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
                            this.selectedItemId = null;
                            this.cashReceived = 0;
                            this.paymentModal = false;
                            this.resetPayments();

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
