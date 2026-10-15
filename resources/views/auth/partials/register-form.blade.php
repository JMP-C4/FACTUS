<div class="mx-auto w-full max-w-md">
    <div
        class="border-2
            border-[var(--color-secondary)]
            bg-[var(--color-surface)]
            p-6
            shadow-[10px_10px_0_var(--color-accent)]
            sm:p-8"
    >
        <div class="mb-8">
            <div class="mb-4 flex items-center gap-3">
                <div
                    class="flex
                        h-11
                        w-11
                        items-center
                        justify-center
                        border-2
                        border-[var(--color-secondary)]
                        bg-[var(--color-surface-muted)]"
                >
                    <svg
                        class="h-6 w-6 text-[var(--color-accent)]"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M12 5a3 3 0 100 6 3 3 0 000-6z" />
                        <path d="M5 19a7 7 0 0114 0" />
                        <path d="M19 8v4M21 10h-4" />
                    </svg>
                </div>

                <div>
                    <p
                        class="text-xs
                            font-bold
                            uppercase
                            tracking-[0.3em]
                            text-[var(--color-text-soft)]"
                    >
                        Nuevo acceso
                    </p>
                    <h2
                        class="text-2xl
                            font-black
                            uppercase
                            tracking-tight
                            text-[var(--color-secondary)]"
                    >
                        Crear cuenta
                    </h2>
                </div>
            </div>

            <p
                class="text-sm
                    leading-6
                    text-[var(--color-text-muted)]"
            >
                Este formulario solo crea el usuario base. El rol y el alcance se
                asignan después desde el panel administrativo.
            </p>
        </div>

        <div
            class="mb-6
                border-2
                border-[var(--color-secondary)]
                bg-[rgba(245,158,11,0.12)]
                px-4
                py-3
                text-sm
                leading-6
                text-[var(--color-text)]"
        >
            Campos alineados con migraciones actuales:
            <span class="font-semibold">name</span>,
            <span class="font-semibold">email</span> y
            <span class="font-semibold">password</span>.
        </div>

        <form
            class="flex
                flex-col
                gap-6
                font-sans"
            method="POST"
            action="{{ route('register') }}"
        >
            @csrf

            <div class="group relative">
                <label
                    for="name"
                    class="mb-1 block text-xs font-bold uppercase tracking-[0.25em] text-[var(--color-text-soft)]"
                >
                    Nombre completo
                </label>

                <div class="relative">
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        class="block
                            w-full
                            border-2
                            border-[var(--color-secondary)]
                            bg-[var(--color-surface)]
                            p-3.5
                            text-sm
                            font-medium
                            text-[var(--color-text)]
                            outline-none
                            transition-colors
                            duration-200
                            ease-in-out
                            placeholder:text-[var(--color-text-soft)]
                            focus:border-[var(--color-primary)]"
                        placeholder="Juan Parada"
                        required
                        autofocus
                        autocomplete="name"
                    />

                    <div
                        class="absolute left-0 top-0 -z-10 h-full w-full translate-x-1.5 translate-y-1.5 border-2 border-transparent bg-[var(--color-surface-muted)] transition-transform group-focus-within:translate-x-2.5 group-focus-within:translate-y-2.5"
                    ></div>
                </div>

                @error('name')
                    <p class="mt-2 text-sm font-medium text-[var(--color-danger)]">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="group relative">
                <label
                    for="email"
                    class="mb-1 block text-xs font-bold uppercase tracking-[0.25em] text-[var(--color-text-soft)]"
                >
                    Correo electronico
                </label>

                <div class="relative">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="block
                            w-full
                            border-2
                            border-[var(--color-secondary)]
                            bg-[var(--color-surface)]
                            p-3.5
                            text-sm
                            font-medium
                            text-[var(--color-text)]
                            outline-none
                            transition-colors
                            duration-200
                            ease-in-out
                            placeholder:text-[var(--color-text-soft)]
                            focus:border-[var(--color-primary)]"
                        placeholder="admin@factus.local"
                        required
                        autocomplete="username"
                    />

                    <div
                        class="absolute left-0 top-0 -z-10 h-full w-full translate-x-1.5 translate-y-1.5 border-2 border-transparent bg-[var(--color-surface-muted)] transition-transform group-focus-within:translate-x-2.5 group-focus-within:translate-y-2.5"
                    ></div>
                </div>

                @error('email')
                    <p class="mt-2 text-sm font-medium text-[var(--color-danger)]">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div class="group relative">
                    <label
                        for="password"
                        class="mb-1 block text-xs font-bold uppercase tracking-[0.25em] text-[var(--color-text-soft)]"
                    >
                        Contrasena
                    </label>

                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="block
                                w-full
                                border-2
                                border-[var(--color-secondary)]
                                bg-[var(--color-surface)]
                                p-3.5
                                text-sm
                                font-medium
                                text-[var(--color-text)]
                                outline-none
                                transition-colors
                                duration-200
                                ease-in-out
                                placeholder:text-[var(--color-text-soft)]
                                focus:border-[var(--color-primary)]"
                            placeholder="Min. 8 caracteres"
                            required
                            autocomplete="new-password"
                        />

                        <div
                            class="absolute left-0 top-0 -z-10 h-full w-full translate-x-1.5 translate-y-1.5 border-2 border-transparent bg-[var(--color-surface-muted)] transition-transform group-focus-within:translate-x-2.5 group-focus-within:translate-y-2.5"
                        ></div>
                    </div>

                    @error('password')
                        <p class="mt-2 text-sm font-medium text-[var(--color-danger)]">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="group relative">
                    <label
                        for="password_confirmation"
                        class="mb-1 block text-xs font-bold uppercase tracking-[0.25em] text-[var(--color-text-soft)]"
                    >
                        Confirmar
                    </label>

                    <div class="relative">
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="block
                                w-full
                                border-2
                                border-[var(--color-secondary)]
                                bg-[var(--color-surface)]
                                p-3.5
                                text-sm
                                font-medium
                                text-[var(--color-text)]
                                outline-none
                                transition-colors
                                duration-200
                                ease-in-out
                                placeholder:text-[var(--color-text-soft)]
                                focus:border-[var(--color-primary)]"
                            placeholder="Repite la contrasena"
                            required
                            autocomplete="new-password"
                        />

                        <div
                            class="absolute left-0 top-0 -z-10 h-full w-full translate-x-1.5 translate-y-1.5 border-2 border-transparent bg-[var(--color-surface-muted)] transition-transform group-focus-within:translate-x-2.5 group-focus-within:translate-y-2.5"
                        ></div>
                    </div>

                    @error('password_confirmation')
                        <p class="mt-2 text-sm font-medium text-[var(--color-danger)]">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <a
                    class="font-semibold
                        text-[var(--color-secondary)]
                        underline
                        decoration-[var(--color-primary)]
                        decoration-2
                        underline-offset-4"
                    href="{{ route('login') }}"
                >
                    Ya tienes acceso? Inicia sesion
                </a>

                <button
                    type="submit"
                    class="group
                        relative
                        inline-block"
                >
                    <span
                        class="absolute
                            left-0
                            top-0
                            h-full
                            w-full
                            translate-x-1.5
                            translate-y-1.5
                            transform
                            border-2
                            border-[var(--color-secondary)]
                            bg-[var(--color-accent)]
                            transition-all
                            duration-200
                            ease-out
                            group-hover:translate-x-0
                            group-hover:translate-y-0"
                    ></span>
                    <span
                        class="relative
                            block
                            w-full
                            border-2
                            border-[var(--color-secondary)]
                            bg-[var(--color-surface)]
                            px-5
                            py-3.5
                            text-sm
                            font-bold
                            uppercase
                            tracking-[0.28em]
                            text-[var(--color-secondary)]"
                    >
                        Crear usuario
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
