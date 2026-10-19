{{--
    Modal de pago del POS.

    Solicita el medio de pago y confirma la venta. Se muestra al pulsar el
    botón "Pagar" del carrito. Vive dentro del scope Alpine del POS, por lo
    que comparte el estado (total, pagos, splitMode, etc.).
--}}
<div
    x-cloak
    x-show="paymentModal"
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    role="dialog"
    aria-modal="true"
    aria-label="Registrar pago de la venta"
>
    <div class="w-full max-w-lg max-h-[92vh] overflow-y-auto border-[3px] border-ink bg-surface p-6 shadow-[8px_8px_0_var(--color-primary)]">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="brutal-kicker text-lg">Registrar pago</h3>
                <p class="mt-1 text-sm text-muted">
                    Selecciona el medio de pago para cobrar
                    <span class="font-mono font-bold" x-text="money(total)"></span>.
                </p>
            </div>
            <button type="button" class="text-xl leading-none" @click="closePayment()" aria-label="Cerrar">✕</button>
        </div>

        <div class="mt-4 border-[2px] border-ink p-3 space-y-3">
            <div class="flex items-center justify-between">
                <span class="brutal-kicker text-xs">Medio de pago</span>
                <button type="button" class="brutal-label text-[10px] underline" @click="toggleSplit()">
                    <span x-text="splitMode ? 'Pago único' : 'Dividir pago'"></span>
                </button>
            </div>

            {{-- Pago único en efectivo --}}
            <template x-if="!splitMode">
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="brutal-label text-[10px]">Recibido</span>
                        <input type="number" min="0" step="0.01" x-model.number="cashReceived"
                               class="brutal-input !w-32 !py-1 text-right font-mono">
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="brutal-label text-[10px]">Cambio</span>
                        <span class="font-mono" x-text="money(change > 0 ? change : 0)"></span>
                    </div>
                    <p x-show="change < 0" class="text-danger text-xs">El monto recibido es menor al total.</p>
                </div>
            </template>

            {{-- Pago dividido en dos o más partes --}}
            <template x-if="splitMode">
                <div class="space-y-3">
                    <template x-for="(payment, index) in payments" :key="index">
                        <div class="border-[2px] border-ink p-2 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="brutal-kicker text-[10px] text-muted" x-text="'Parte ' + (index + 1)"></span>
                                <button type="button" class="text-danger text-xs"
                                        x-show="payments.length > 1"
                                        :aria-label="'Quitar parte ' + (index + 1)"
                                        @click="removePayment(index)">✕</button>
                            </div>

                            <div class="flex items-center gap-2">
                                <select x-model="payment.method" @change="syncCashReceived(payment)"
                                        class="brutal-input !py-1 text-xs flex-1"
                                        :aria-label="'Método de pago de la parte ' + (index + 1)">
                                    <option value="cash">Efectivo</option>
                                    <option value="debit_card">Tarjeta débito</option>
                                    <option value="credit_card">Tarjeta crédito</option>
                                    <option value="transfer">Transferencia</option>
                                </select>
                                <input type="number" min="0" step="0.01" x-model.number="payment.amount"
                                       :max="total"
                                       @input="onSplitAmount(payment)"
                                       class="brutal-input !w-28 !py-1 text-right font-mono text-xs"
                                       :aria-label="'Monto de la parte ' + (index + 1)">
                            </div>

                            <div x-show="payment.method === 'cash'" class="space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="brutal-label text-[10px]">Recibido</span>
                                    <input type="number" min="0" step="0.01" x-model.number="payment.cash_received"
                                           class="brutal-input !w-28 !py-0.5 text-right font-mono">
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="brutal-label text-[10px]">Cambio</span>
                                    <span class="font-mono" x-text="money((payment.cash_received || 0) - (payment.amount || 0) > 0 ? (payment.cash_received - payment.amount) : 0)"></span>
                                </div>
                            </div>

                            <div x-show="payment.method === 'debit_card' || payment.method === 'credit_card'" class="flex gap-2">
                                <input type="text" maxlength="4" placeholder="Últimos 4" x-model="payment.card_last_four"
                                       class="brutal-input !py-0.5 text-xs w-1/3">
                                <input type="text" placeholder="Nº aprobación" x-model="payment.card_approval_code"
                                       class="brutal-input !py-0.5 text-xs flex-1">
                            </div>

                            <div x-show="payment.method === 'transfer'" class="flex gap-2">
                                <input type="text" placeholder="Referencia" x-model="payment.reference_number"
                                       class="brutal-input !py-0.5 text-xs flex-1">
                                <input type="text" placeholder="Banco" x-model="payment.bank_entity"
                                       class="brutal-input !py-0.5 text-xs w-1/3">
                            </div>
                        </div>
                    </template>

                    <button type="button" class="brutal-button w-full !py-1 text-xs" @click="addPayment()">
                        + Añadir parte
                    </button>
                </div>
            </template>

            {{-- Resumen: así queda la venta con los pagos registrados --}}
            <div class="pos-payment-preview p-3 space-y-1" aria-live="polite">
                <div class="flex justify-between text-xs">
                    <span class="brutal-label text-[10px]">Total a pagar</span>
                    <span class="font-mono font-bold" x-text="money(total)"></span>
                </div>
                <template x-for="line in paymentSummary" :key="line.key">
                    <div class="flex justify-between text-xs">
                        <span class="text-muted" x-text="line.label"></span>
                        <span class="font-mono" x-text="money(line.amount)"></span>
                    </div>
                </template>
                <div class="flex justify-between text-xs border-t-[2px] border-ink pt-1">
                    <span class="brutal-label text-[10px]">Pagado</span>
                    <span class="font-mono" :class="paymentsValid ? 'text-success' : 'text-danger'"
                          x-text="money(paidTotal)"></span>
                </div>
                <div class="flex justify-between text-xs" x-show="remaining > 0.009">
                    <span class="brutal-label text-[10px]">Falta por cubrir</span>
                    <span class="font-mono text-danger" x-text="money(remaining)"></span>
                </div>
                <div class="flex justify-between text-xs" x-show="!splitMode && change > 0.009">
                    <span class="brutal-label text-[10px]">Cambio</span>
                    <span class="font-mono text-success" x-text="money(change)"></span>
                </div>
            </div>

            <p x-show="!paymentsValid" class="text-[10px] text-danger">
                La suma de los pagos debe coincidir exactamente con el total.
            </p>
        </div>

        <p x-show="error" class="brutal-alert mt-3 bg-danger text-white text-xs" x-text="error"></p>

        <div class="mt-5 flex gap-2">
            <button type="button" class="brutal-button flex-1" @click="closePayment()">Cancelar</button>
            <button
                type="button"
                class="brutal-button flex-1 !bg-success !text-white disabled:opacity-50"
                :disabled="processing || items.length === 0 || !paymentsValid"
                @click="checkout()"
            >
                <span x-show="!processing">Confirmar venta · <span x-text="money(total)"></span></span>
                <span x-show="processing">Procesando...</span>
            </button>
        </div>
    </div>
</div>
