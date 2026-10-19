{{--
    Modal de autorización para retirar un producto del carrito.

    Se abre al pulsar el botón "Retirar" de una línea del carrito o con el
    atajo "Supr". Exige un código de autorización o credenciales de un
    superior (ver PosController::authorizeRemoval).
--}}
<div
    x-cloak
    x-show="removalModal"
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    role="dialog"
    aria-modal="true"
    aria-label="Autorización para retirar producto"
>
    <div class="w-full max-w-md border-[3px] border-ink bg-surface p-6 shadow-[8px_8px_0_var(--color-primary)]">
        <h3 class="brutal-kicker text-lg">Retirar producto</h3>
        <p class="mt-1 text-sm text-muted">
            Se requiere autorización de un superior para retirar
            <span class="font-semibold text-ink" x-text="removalTargetName || 'el artículo'"></span>
            del carrito.
        </p>

        <div class="mt-4 space-y-3">
            <template x-if="removalMethod === 'code'">
                <div>
                    <label class="brutal-label mb-1 block text-[10px]">Código de autorización</label>
                    <input
                        type="password"
                        x-model="removalCode"
                        @keydown.enter.prevent="confirmRemoval()"
                        class="brutal-input"
                        placeholder="••••••"
                        autocomplete="off"
                    >
                </div>
            </template>

            <template x-if="removalMethod === 'credentials'">
                <div class="space-y-3">
                    <div>
                        <label class="brutal-label mb-1 block text-[10px]">Email del supervisor</label>
                        <input type="email" x-model="removalEmail" class="brutal-input"
                               placeholder="supervisor@empresa.com" autocomplete="off">
                    </div>
                    <div>
                        <label class="brutal-label mb-1 block text-[10px]">Contraseña</label>
                        <input type="password" x-model="removalPassword" @keydown.enter.prevent="confirmRemoval()"
                               class="brutal-input" placeholder="••••••" autocomplete="off">
                    </div>
                </div>
            </template>

            <p x-show="removalError" class="brutal-alert bg-danger text-white text-xs" x-text="removalError"></p>

            <button
                type="button"
                class="brutal-label text-[10px] underline"
                @click="removalMethod = removalMethod === 'code' ? 'credentials' : 'code'; removalError = ''"
            >
                <span x-text="removalMethod === 'code' ? 'Usar credenciales de supervisor' : 'Usar código de autorización'"></span>
            </button>
        </div>

        <div class="mt-5 flex gap-2">
            <button type="button" class="brutal-button flex-1" @click="closeRemoval()">Cancelar</button>
            <button
                type="button"
                class="brutal-button flex-1 !bg-danger !text-white disabled:opacity-50"
                @click="confirmRemoval()"
                :disabled="removalLoading"
            >
                <span x-show="!removalLoading">Autorizar y retirar</span>
                <span x-show="removalLoading">Verificando...</span>
            </button>
        </div>
    </div>
</div>
