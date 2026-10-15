<div class="hidden lg:block">
    <div class="max-w-xl">
        <p
            class="mb-4
                text-xs
                font-bold
                uppercase
                tracking-[0.35em]
                text-[var(--color-text-soft)]"
        >
            Onboarding interno
        </p>
        <h1
            class="text-4xl
                font-black
                uppercase
                leading-none
                tracking-tight
                text-[var(--color-secondary)]
                xl:text-5xl"
        >
            Crea tu acceso base y deja el rol para el control administrativo
        </h1>
        <p
            class="mt-6
                max-w-lg
                text-base
                leading-7
                text-[var(--color-text-muted)]"
        >
            El registro crea el usuario principal con sus credenciales. El rol,
            el alcance por compañía y la sucursal se asignan luego desde el
            módulo de Access Control.
        </p>

        <div class="mt-10 grid gap-4 sm:grid-cols-2">
            <div
                class="border-2
                    border-[var(--color-secondary)]
                    bg-[var(--color-surface)]
                    p-5
                    shadow-[6px_6px_0_var(--color-surface-muted)]"
            >
                <p
                    class="text-xs
                        font-bold
                        uppercase
                        tracking-[0.28em]
                        text-[var(--color-text-soft)]"
                >
                    Registro
                </p>
                <p
                    class="mt-3
                        text-sm
                        leading-6
                        text-[var(--color-text-muted)]"
                >
                    Captura nombre, correo y contraseña de acuerdo con la
                    estructura actual de la tabla `users`.
                </p>
            </div>

            <div
                class="border-2
                    border-[var(--color-secondary)]
                    bg-[var(--color-surface)]
                    p-5
                    shadow-[6px_6px_0_var(--color-surface-muted)]"
            >
                <p
                    class="text-xs
                        font-bold
                        uppercase
                        tracking-[0.28em]
                        text-[var(--color-text-soft)]"
                >
                    Access Control
                </p>
                <p
                    class="mt-3
                        text-sm
                        leading-6
                        text-[var(--color-text-muted)]"
                >
                    Después del alta, un `super_admin` asigna el rol correcto y
                    define el scope global, por compañía o por sucursal.
                </p>
            </div>
        </div>
    </div>
</div>
