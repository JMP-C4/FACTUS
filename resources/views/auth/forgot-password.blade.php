<x-guest-layout>
    <section class="min-h-screen bg-[var(--color-bg)] px-6 py-10 text-[var(--color-ink)]">
        <div class="mx-auto flex min-h-[calc(100vh-5rem)] w-full max-w-lg items-center justify-center">
            <div class="w-full">
                <div class="brutal-card mb-6">
                    <h1 class="mb-4 text-3xl uppercase">{{ __('Forgot Password') }}</h1>
                    <p class="text-sm text-[var(--color-text-muted)]">
                        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                    </p>
                </div>

                @if (session('status'))
                    <div class="brutal-alert mb-4 bg-[var(--color-success)] text-white">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="brutal-card">
                        <div class="mb-6">
                            <label for="email" class="brutal-label mb-2 block">{{ __('Email') }}</label>
                            <input
                                id="email"
                                class="brutal-input"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                            />
                            @error('email')
                                <p class="mt-2 text-sm text-[var(--color-danger)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="brutal-button w-full">
                            {{ __('Email Password Reset Link') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</x-guest-layout>
