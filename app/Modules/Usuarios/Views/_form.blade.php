@php($user = $user ?? null)
@php($currentRoleId = old('role_id', $user?->roles->first()?->id))

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
        <h3 class="brutal-kicker mb-4 text-sm">Datos del Usuario</h3>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="name" class="brutal-label mb-1 block">Nombre *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user?->name) }}" class="brutal-input" required>
                @error('name')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="brutal-label mb-1 block">Email *</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user?->email) }}" class="brutal-input" required>
                @error('email')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password" class="brutal-label mb-1 block">Contraseña {{ $user ? '(opcional)' : '*' }}</label>
                <input type="password" id="password" name="password" class="brutal-input" {{ $user ? '' : 'required' }} autocomplete="new-password">
                @error('password')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password_confirmation" class="brutal-label mb-1 block">Confirmar contraseña</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="brutal-input" autocomplete="new-password">
            </div>
        </div>
    </div>

    <div>
        <h3 class="brutal-kicker mb-4 text-sm">Acceso y Alcance</h3>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label for="role_id" class="brutal-label mb-1 block">Rol</label>
                <select id="role_id" name="role_id" class="brutal-input">
                    <option value="">Sin rol</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ (int) $currentRoleId === $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('role_id')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="company_id" class="brutal-label mb-1 block">Compañía</label>
                <select id="company_id" name="company_id" class="brutal-input">
                    <option value="">Global</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ (int) old('company_id', $user?->company_id) === $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                    @endforeach
                </select>
                @error('company_id')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="branch_id" class="brutal-label mb-1 block">Sucursal</label>
                <select id="branch_id" name="branch_id" class="brutal-input">
                    <option value="">Todas</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ (int) old('branch_id', $user?->branch_id) === $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
                @error('branch_id')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div>
        <h3 class="brutal-kicker mb-1 text-sm">Permisos adicionales de este usuario</h3>
        <p class="mb-4 text-xs text-muted">
            Los permisos marcados como heredados vienen del rol y no se pueden quitar aquí.
            Los que marques en esta sección se asignan <strong>solo a este usuario</strong>:
            no cambian el rol ni afectan a otros usuarios con el mismo rol.
        </p>

        @php($permissionsGroupedByModule = $permissionsGroupedByModule ?? collect())
        @php($rolePermissionIds = $rolePermissionIds ?? [])
        @php($directPermissionIds = $directPermissionIds ?? [])
        @php($grantablePermissionIds = $grantablePermissionIds ?? null)

        @foreach($permissionsGroupedByModule as $module => $permissions)
            <div class="perm-group mb-4 border-[2px] border-ink p-4" x-data="{ all: false }">
                <div class="mb-3 flex items-center justify-between">
                    <h4 class="brutal-kicker text-xs">{{ ucfirst($module) }}</h4>
                    <label class="flex cursor-pointer items-center gap-2">
                        <input type="checkbox" class="h-4 w-4" x-model="all"
                            @change="$el.closest('.perm-group').querySelectorAll('.perm-extra:not([disabled])').forEach(c => c.checked = all)">
                        <span class="brutal-label text-[10px]">Todos del grupo</span>
                    </label>
                </div>

                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($permissions as $permission)
                        @php($inherited = in_array($permission->id, $rolePermissionIds, true))
                        @php($direct = in_array($permission->id, $directPermissionIds, true))
                        @php($grantable = $grantablePermissionIds === null || in_array($permission->id, $grantablePermissionIds, true))
                        <label class="flex items-start gap-2 {{ $inherited || ! $grantable ? 'opacity-60' : 'cursor-pointer' }}">
                            <input type="checkbox"
                                class="perm-extra mt-1 h-4 w-4"
                                @if($inherited) checked disabled
                                @elseif(! $grantable) disabled
                                @elseif($direct) checked
                                @endif
                                @unless($inherited || ! $grantable) name="permissions[]" value="{{ $permission->id }}" @endunless>
                            <span>
                                <span class="block text-sm font-semibold">{{ $permission->name }}</span>
                                <span class="block text-xs text-muted">{{ $permission->description }}</span>
                                @if($inherited)
                                    <span class="block text-[10px] font-semibold text-success">Heredado del rol</span>
                                @elseif(! $grantable)
                                    <span class="block text-[10px] font-semibold text-danger">No puedes otorgarlo</span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex items-center justify-end gap-4 border-t-[2px] border-ink pt-4">
        <a href="{{ route('usuarios.index') }}" class="brutal-button">Cancelar</a>
        <button type="submit" class="brutal-button !bg-primary !text-white">{{ $submitLabel ?? 'Guardar' }}</button>
    </div>
</div>
