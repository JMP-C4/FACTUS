<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Editar Cliente</h2>
            <a href="{{ route('clientes.index') }}" class="brutal-button">
                Volver
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('clientes.update', $customer) }}">
            @csrf
            @method('PUT')

            <div class="brutal-card space-y-6">
                {{-- Datos de identificación --}}
                <div>
                    <h3 class="brutal-kicker mb-4 text-sm">Identificación</h3>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="identification_document_code" class="brutal-label mb-1 block">Tipo de Documento *</label>
                            <select id="identification_document_code" name="identification_document_code" class="brutal-input" required>
                                <option value="13" {{ old('identification_document_code', $customer->identification_document_code) === '13' ? 'selected' : '' }}>Cédula de Ciudadanía</option>
                                <option value="31" {{ old('identification_document_code', $customer->identification_document_code) === '31' ? 'selected' : '' }}>NIT</option>
                                <option value="22" {{ old('identification_document_code', $customer->identification_document_code) === '22' ? 'selected' : '' }}>Cédula de Extranjería</option>
                                <option value="11" {{ old('identification_document_code', $customer->identification_document_code) === '11' ? 'selected' : '' }}>Registro Civil</option>
                                <option value="12" {{ old('identification_document_code', $customer->identification_document_code) === '12' ? 'selected' : '' }}>Tarjeta de Identidad</option>
                            </select>
                            @error('identification_document_code')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="document_number" class="brutal-label mb-1 block">Número de Documento *</label>
                            <input
                                type="text"
                                id="document_number"
                                name="document_number"
                                value="{{ old('document_number', $customer->document_number) }}"
                                class="brutal-input"
                                required
                            >
                            @error('document_number')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Datos de organización --}}
                <div>
                    <h3 class="brutal-kicker mb-4 text-sm">Organización y Tributo</h3>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="legal_organization_code" class="brutal-label mb-1 block">Tipo de Organización *</label>
                            <select id="legal_organization_code" name="legal_organization_code" class="brutal-input" required>
                                <option value="22" {{ old('legal_organization_code', $customer->legal_organization_code) === '22' ? 'selected' : '' }}>Persona Natural</option>
                                <option value="23" {{ old('legal_organization_code', $customer->legal_organization_code) === '23' ? 'selected' : '' }}>Persona Jurídica</option>
                            </select>
                            @error('legal_organization_code')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tribute_code" class="brutal-label mb-1 block">Tipo de Tributo *</label>
                            <select id="tribute_code" name="tribute_code" class="brutal-input" required>
                                <option value="ZZ" {{ old('tribute_code', $customer->tribute_code) === 'ZZ' ? 'selected' : '' }}>No Aplica</option>
                                <option value="OC" {{ old('tribute_code', $customer->tribute_code) === 'OC' ? 'selected' : '' }}>Otro</option>
                                <option value="01" {{ old('tribute_code', $customer->tribute_code) === '01' ? 'selected' : '' }}>IVA</option>
                            </select>
                            @error('tribute_code')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="municipality_code" class="brutal-label mb-1 block">Código Municipio</label>
                            <input
                                type="text"
                                id="municipality_code"
                                name="municipality_code"
                                value="{{ old('municipality_code', $customer->municipality_code) }}"
                                class="brutal-input"
                                placeholder="Ej: 11001"
                            >
                            @error('municipality_code')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Datos personales --}}
                <div>
                    <h3 class="brutal-kicker mb-4 text-sm">Datos Personales</h3>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="first_name" class="brutal-label mb-1 block">Nombre *</label>
                            <input
                                type="text"
                                id="first_name"
                                name="first_name"
                                value="{{ old('first_name', $customer->first_name) }}"
                                class="brutal-input"
                                required
                            >
                            @error('first_name')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="last_name" class="brutal-label mb-1 block">Apellido</label>
                            <input
                                type="text"
                                id="last_name"
                                name="last_name"
                                value="{{ old('last_name', $customer->last_name) }}"
                                class="brutal-input"
                            >
                            @error('last_name')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="company_name" class="brutal-label mb-1 block">Razón Social</label>
                            <input
                                type="text"
                                id="company_name"
                                name="company_name"
                                value="{{ old('company_name', $customer->company_name) }}"
                                class="brutal-input"
                            >
                            @error('company_name')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Datos de contacto --}}
                <div>
                    <h3 class="brutal-kicker mb-4 text-sm">Contacto</h3>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="email" class="brutal-label mb-1 block">Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email', $customer->email) }}"
                                class="brutal-input"
                            >
                            @error('email')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="brutal-label mb-1 block">Teléfono</label>
                            <input
                                type="text"
                                id="phone"
                                name="phone"
                                value="{{ old('phone', $customer->phone) }}"
                                class="brutal-input"
                            >
                            @error('phone')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="address" class="brutal-label mb-1 block">Dirección</label>
                            <input
                                type="text"
                                id="address"
                                name="address"
                                value="{{ old('address', $customer->address) }}"
                                class="brutal-input"
                            >
                            @error('address')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="billing_email" class="brutal-label mb-1 block">Email de Facturación</label>
                            <input
                                type="email"
                                id="billing_email"
                                name="billing_email"
                                value="{{ old('billing_email', $customer->billing_email) }}"
                                class="brutal-input"
                            >
                            @error('billing_email')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="flex items-center justify-end gap-4 pt-4 border-t-[2px] border-ink">
                    <a href="{{ route('clientes.index') }}" class="brutal-button">
                        Cancelar
                    </a>
                    <button type="submit" class="brutal-button !bg-primary !text-white">
                        Actualizar Cliente
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
