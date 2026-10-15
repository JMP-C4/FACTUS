{{-- Columna auxiliar de alertas estratégicas.
     Se mantiene separada para convertir estas tarjetas en componentes dinámicos más adelante. --}}
<div class="space-y-5">
    <div class="brutal-alert bg-[rgba(22,163,74,0.16)]">
        <div class="mt-1 h-3 w-3 shrink-0 border-2 border-black bg-[var(--color-success)]"></div>
        <div>
            <p class="font-display text-sm font-black uppercase">RBAC operativo</p>
            <p class="mt-1 text-sm leading-6 text-[var(--color-text-muted)]">
                Migraciones, modelos, seeders y bootstrap del super admin ya forman una base visible.
            </p>
        </div>
    </div>

    <div class="brutal-alert bg-[rgba(245,158,11,0.18)]">
        <div class="mt-1 h-3 w-3 shrink-0 border-2 border-black bg-[var(--color-accent)]"></div>
        <div>
            <p class="font-display text-sm font-black uppercase">Siguiente entrega</p>
            <p class="mt-1 text-sm leading-6 text-[var(--color-text-muted)]">
                Integrar el scope company/branch en middleware y ampliar la facturación local con reportes y exportes.
            </p>
        </div>
    </div>

    <div class="brutal-alert bg-[rgba(220,38,38,0.14)]">
        <div class="mt-1 h-3 w-3 shrink-0 border-2 border-black bg-[var(--color-danger)]"></div>
        <div>
            <p class="font-display text-sm font-black uppercase">Observabilidad</p>
            <p class="mt-1 text-sm leading-6 text-[var(--color-text-muted)]">
                La emisión local registra cada comprobante en un log interno con payload, respuesta y estado.
            </p>
        </div>
    </div>
</div>
