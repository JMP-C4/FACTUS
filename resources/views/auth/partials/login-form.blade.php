@php
    $hasPasswordRequestRoute = \Illuminate\Support\Facades\Route::has('password.request');
@endphp

<div class="mx-auto w-full max-w-md">
    <div
        class="border-2
            border-[var(--color-secondary)]
            bg-[var(--color-surface)]
            p-6
            shadow-[10px_10px_0_var(--color-primary)]
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
                        class="h-6 w-6 text-[var(--color-primary)]"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5v9A2.5 2.5 0 0 1 17.5 19h-11A2.5 2.5 0 0 1 4 16.5z" />
                        <path d="M8 9h8M8 12h8M8 15h5" />
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
                        Acceso
                    </p>
                    <h2
                        class="text-2xl
                            font-black
                            uppercase
                            tracking-tight
                            text-[var(--color-secondary)]"
                    >
                        Iniciar sesion
                    </h2>
                </div>
            </div>

            <p
                class="text-sm
                    leading-6
                    text-[var(--color-text-muted)]"
            >
                Usa tus credenciales para entrar al sistema.
            </p>
        </div>

        @if (session('status'))
            <div
                class="mb-5
                    border-2
                    border-[var(--color-success)]
                    bg-[color:color-mix(in_srgb,var(--color-success)_12%,white)]
                    px-4
                    py-3
                    text-sm
                    text-[var(--color-text)]"
            >
                {{ session('status') }}
            </div>
        @endif

        <form
            class="flex
                flex-col
                gap-6
                font-sans"
            method="POST"
            action="{{ route('login') }}"
        >
            @csrf

            <div class="group relative">
                <label
                    for="email"
                    class="mb-1 block text-xs font-bold uppercase tracking-[0.25em] text-[var(--color-text-soft)]"
                >
                    Correo electronico
                </label>

                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg
                            class="h-5 w-5 text-[var(--color-secondary)]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"
                            />
                        </svg>
                    </div>

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
                            pl-10
                            text-sm
                            font-medium
                            text-[var(--color-text)]
                            outline-none
                            transition-colors
                            duration-200
                            ease-in-out
                            placeholder:text-[var(--color-text-soft)]
                            focus:border-[var(--color-primary)]"
                        placeholder="nombre@ejemplo.com"
                        required
                        autofocus
                        autocomplete="username"
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                        aria-describedby="{{ $errors->has('email') ? 'email-error' : '' }}"
                    />

                    <div
                        class="absolute left-0 top-0 -z-10 h-full w-full translate-x-1.5 translate-y-1.5 border-2 border-transparent bg-[var(--color-surface-muted)] transition-transform group-focus-within:translate-x-2.5 group-focus-within:translate-y-2.5"
                    ></div>
                </div>

                @error('email')
                    <p
                        id="email-error"
                        role="alert"
                        class="mt-2 text-sm font-medium text-[var(--color-danger)]"
                    >
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="group relative">
                <label
                    for="password"
                    class="mb-1 block text-xs font-bold uppercase tracking-[0.25em] text-[var(--color-text-soft)]"
                >
                    Contrasena
                </label>

                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg
                            class="h-5 w-5 text-[var(--color-secondary)]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                            />
                        </svg>
                    </div>

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
                            pl-10
                            text-sm
                            font-medium
                            text-[var(--color-text)]
                            outline-none
                            transition-colors
                            duration-200
                            ease-in-out
                            placeholder:text-[var(--color-text-soft)]
                            focus:border-[var(--color-primary)]"
                        placeholder="Password"
                        required
                        autocomplete="current-password"
                    />

                    <div
                        class="absolute left-0 top-0 -z-10 h-full w-full translate-x-1.5 translate-y-1.5 border-2 border-transparent bg-[var(--color-surface-muted)] transition-transform group-focus-within:translate-x-2.5 group-focus-within:translate-y-2.5"
                    ></div>
                </div>

                @error('password')
                    <p
                        class="mt-2 text-sm font-medium text-[var(--color-danger)]"
                    >
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div
                class="flex
                    flex-col
                    gap-3
                    text-sm
                    text-[var(--color-text-muted)]
                    sm:flex-row
                    sm:items-center
                    sm:justify-between"
            >
                <label
                    for="remember_me"
                    class="inline-flex
                        items-center
                        gap-2
                        font-medium"
                >
                    <input
                        id="remember_me"
                        type="checkbox"
                        name="remember"
                        class="h-4
                            w-4
                            border-[var(--color-border)]
                            text-[var(--color-primary)]
                            focus:ring-[var(--color-primary)]"
                    >
                    <span>Recordarme</span>
                </label>

                @if ($hasPasswordRequestRoute)
                    <a
                        class="font-semibold
                            text-[var(--color-secondary)]
                            underline
                            decoration-[var(--color-primary)]
                            decoration-2
                            underline-offset-4"
                        href="{{ route('password.request') }}"
                    >
                        Olvide mi contrasena
                    </a>
                @endif
            </div>

            <button
                type="submit"
                class="group
                    relative
                    mt-2
                    inline-block
                    w-full"
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
                        bg-[var(--color-primary)]
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
                    Entrar al sistema
                </span>
            </button>
        </form>

        <p
            class="mt-8
                text-center
                text-sm
                text-[var(--color-text-muted)]"
        >
            No tienes cuenta?
            <a
                href="{{ route('register') }}"
                class="font-semibold
                    text-[var(--color-primary)]
                    underline
                    underline-offset-4"
            >
                Registrate
            </a>
        </p>

        <p
            class="mt-4
                text-center
                text-xs
                leading-6
                text-[var(--color-text-soft)]"
        >
            El acceso se autentica con correo y contraseña. Los roles y scopes
            se administran después desde Access Control.
        </p>
    </div>
</div>
