<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Detalle del Cliente</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('clientes.edit', $customer) }}" class="brutal-button">
                    Editar
                </a>
                <a href="{{ route('clientes.index') }}" class="brutal-button">
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="brutal-card space-y-6">
            {{-- Encabezado --}}
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-xl font-bold">{{ $customer->display_name }}</h3>
                    <p class="mt-1 text-sm text-muted">{{ $customer->identification_document_code }}: {{ $customer->document_number }}</p>
                </div>
                @if($customer->is_facturable)
                    <span class="brutal-badge bg-success text-white">Facturable</span>
                @else
                    <span class="brutal-badge bg-accent text-ink">Incompleto</span>
                @endif
            </div>

            {{-- Datos de identificación --}}
            <div>
                <h4 class="brutal-kicker mb-3 text-xs">Identificación</h4>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Tipo de Documento</span>
                        <p class="font-bold">{{ $customer->identification_document_code }}</p>
                    </div>
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Número</span>
                        <p class="font-mono font-bold">{{ $customer->document_number }}</p>
                    </div>
                </div>
            </div>

            {{-- Datos de organización --}}
            <div>
                <h4 class="brutal-kicker mb-3 text-xs">Organización</h4>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Tipo de Organización</span>
                        <p class="font-bold">{{ $customer->legal_organization_code }}</p>
                    </div>
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Tributo</span>
                        <p class="font-bold">{{ $customer->tribute_code }}</p>
                    </div>
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Municipio</span>
                        <p class="font-bold">{{ $customer->municipality_code ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Datos personales --}}
            <div>
                <h4 class="brutal-kicker mb-3 text-xs">Datos Personales</h4>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Nombre</span>
                        <p class="font-bold">{{ $customer->first_name }}</p>
                    </div>
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Apellido</span>
                        <p class="font-bold">{{ $customer->last_name ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Razón Social</span>
                        <p class="font-bold">{{ $customer->company_name ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Contacto --}}
            <div>
                <h4 class="brutal-kicker mb-3 text-xs">Contacto</h4>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Email</span>
                        <p class="font-bold">{{ $customer->email ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Teléfono</span>
                        <p class="font-bold">{{ $customer->phone ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Dirección</span>
                        <p class="font-bold">{{ $customer->address ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Email de Facturación</span>
                        <p class="font-bold">{{ $customer->billing_email ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Metadatos --}}
            <div class="pt-4 border-t-[2px] border-ink">
                <div class="grid gap-4 sm:grid-cols-2 text-xs text-muted">
                    <div>
                        <span class="brutal-label text-[10px]">Creado</span>
                        <p>{{ $customer->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <span class="brutal-label text-[10px]">Actualizado</span>
                        <p>{{ $customer->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
