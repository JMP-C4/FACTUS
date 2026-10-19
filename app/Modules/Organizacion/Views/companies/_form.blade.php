@php($company = $company ?? null)

@if ($errors->any())
    <x-feedback.alert type="error" title="Revisa los campos" class="mb-5">
        <ul class="list-inside list-disc space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-feedback.alert>
@endif

<div class="brutal-card space-y-6">
    <div>
        <h3 class="brutal-kicker mb-4 text-sm">Datos de la Compañía</h3>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="brutal-label mb-1 block">Nombre *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $company?->name) }}" class="brutal-input" required>
                @error('name')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="nit" class="brutal-label mb-1 block">NIT *</label>
                <input type="text" id="nit" name="nit" value="{{ old('nit', $company?->nit) }}" class="brutal-input" required>
                @error('nit')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="regime" class="brutal-label mb-1 block">Régimen *</label>
                <select id="regime" name="regime" class="brutal-input">
                    <option value="comun" {{ old('regime', $company?->regime ?? 'comun') === 'comun' ? 'selected' : '' }}>Común</option>
                    <option value="simplificado" {{ old('regime', $company?->regime) === 'simplificado' ? 'selected' : '' }}>Simplificado</option>
                </select>
                @error('regime')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="brutal-label mb-1 block">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $company?->email) }}" class="brutal-input">
                @error('email')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="phone" class="brutal-label mb-1 block">Teléfono</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $company?->phone) }}" class="brutal-input">
                @error('phone')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="city" class="brutal-label mb-1 block">Ciudad</label>
                <input type="text" id="city" name="city" value="{{ old('city', $company?->city) }}" class="brutal-input">
                @error('city')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="trial_ends_at" class="brutal-label mb-1 block">Fin de prueba</label>
                <input type="date" id="trial_ends_at" name="trial_ends_at" value="{{ old('trial_ends_at', $company?->trial_ends_at?->format('Y-m-d')) }}" class="brutal-input">
                @error('trial_ends_at')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="address" class="brutal-label mb-1 block">Dirección</label>
                <input type="text" id="address" name="address" value="{{ old('address', $company?->address) }}" class="brutal-input">
                @error('address')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-end">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $company?->is_active ?? true) ? 'checked' : '' }} class="h-4 w-4">
                    <span class="brutal-label">Activa</span>
                </label>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-4 border-t-[2px] border-ink pt-4">
        <a href="{{ route('empresas.index') }}" class="brutal-button">Cancelar</a>
        <button type="submit" class="brutal-button !bg-primary !text-white">{{ $submitLabel ?? 'Guardar' }}</button>
    </div>
</div>
