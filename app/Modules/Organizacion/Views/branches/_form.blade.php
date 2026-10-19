@php($branch = $branch ?? null)
@php($companies = $companies ?? collect())

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
        <h3 class="brutal-kicker mb-4 text-sm">Datos de la Sucursal</h3>

        <div class="grid gap-4 sm:grid-cols-2">
            @if($companies->isNotEmpty())
                <div class="sm:col-span-2">
                    <label for="company_id" class="brutal-label mb-1 block">Compañía *</label>
                    <select id="company_id" name="company_id" class="brutal-input" required>
                        <option value="">Seleccione una compañía</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                        @endforeach
                    </select>
                    @error('company_id')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
            @endif

            <div>
                <label for="name" class="brutal-label mb-1 block">Nombre *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $branch?->name) }}" class="brutal-input" required>
                @error('name')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="code" class="brutal-label mb-1 block">Código *</label>
                <input type="text" id="code" name="code" value="{{ old('code', $branch?->code) }}" class="brutal-input" required>
                @error('code')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="city" class="brutal-label mb-1 block">Ciudad</label>
                <input type="text" id="city" name="city" value="{{ old('city', $branch?->city) }}" class="brutal-input">
                @error('city')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="phone" class="brutal-label mb-1 block">Teléfono</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $branch?->phone) }}" class="brutal-input">
                @error('phone')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="address" class="brutal-label mb-1 block">Dirección</label>
                <input type="text" id="address" name="address" value="{{ old('address', $branch?->address) }}" class="brutal-input">
                @error('address')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div>
        <h3 class="brutal-kicker mb-4 text-sm">Facturación</h3>

        <div class="grid gap-4 sm:grid-cols-4">
            <div>
                <label for="pos_prefix" class="brutal-label mb-1 block">Prefijo POS *</label>
                <input type="text" id="pos_prefix" name="pos_prefix" value="{{ old('pos_prefix', $branch?->pos_prefix ?? '001') }}" class="brutal-input" maxlength="3" required>
                @error('pos_prefix')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="resolution_from" class="brutal-label mb-1 block">Resolución desde *</label>
                <input type="number" id="resolution_from" name="resolution_from" value="{{ old('resolution_from', $branch?->resolution_from ?? 1) }}" class="brutal-input" min="1" required>
                @error('resolution_from')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="resolution_to" class="brutal-label mb-1 block">Resolución hasta *</label>
                <input type="number" id="resolution_to" name="resolution_to" value="{{ old('resolution_to', $branch?->resolution_to ?? 999999999) }}" class="brutal-input" min="1" required>
                @error('resolution_to')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="resolution_date" class="brutal-label mb-1 block">Fecha resolución</label>
                <input type="date" id="resolution_date" name="resolution_date" value="{{ old('resolution_date', $branch?->resolution_date?->format('Y-m-d')) }}" class="brutal-input">
                @error('resolution_date')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div>
        <h3 class="brutal-kicker mb-4 text-sm">Configuración</h3>

        <div class="grid gap-4 sm:grid-cols-4">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_main" value="1" {{ old('is_main', $branch?->is_main) ? 'checked' : '' }} class="h-4 w-4">
                <span class="brutal-label">Principal</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_warehouse" value="1" {{ old('is_warehouse', $branch?->is_warehouse) ? 'checked' : '' }} class="h-4 w-4">
                <span class="brutal-label">Bodega</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="allows_negative_stock" value="1" {{ old('allows_negative_stock', $branch?->allows_negative_stock) ? 'checked' : '' }} class="h-4 w-4">
                <span class="brutal-label">Stock negativo</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $branch?->is_active ?? true) ? 'checked' : '' }} class="h-4 w-4">
                <span class="brutal-label">Activa</span>
            </label>
        </div>
    </div>

    <div class="flex items-center justify-end gap-4 border-t-[2px] border-ink pt-4">
        <a href="{{ route('sucursales.index') }}" class="brutal-button">Cancelar</a>
        <button type="submit" class="brutal-button !bg-primary !text-white">{{ $submitLabel ?? 'Guardar' }}</button>
    </div>
</div>
