@props([
    'logRows' => [],
])

{{-- Tabla de actividad reciente.
     Se deja como parcial independiente para conectar luego logs reales sin tocar la estructura del dashboard. --}}
<div class="brutal-card overflow-hidden">
    <div class="mb-5 flex flex-col gap-3 border-b-[2px] border-black pb-5 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="brutal-kicker text-[var(--color-text-soft)]">System log snapshot</p>
            <h3 class="mt-2 text-2xl font-black uppercase">Actividad reciente</h3>
        </div>

        <span class="brutal-badge bg-[rgba(17,17,17,0.08)]">últimas 24 horas</span>
    </div>

    <div class="overflow-x-auto">
        <table class="brutal-table">
            <thead>
                <tr>
                    <th>Evento</th>
                    <th>Responsable</th>
                    <th>Contexto</th>
                    <th>Estado</th>
                </tr>
            </thead>
        <tbody>
            @forelse ($logRows as $logRow)
                <tr>
                    <td class="font-semibold">{{ $logRow['event'] }}</td>
                    <td>{{ $logRow['owner'] }}</td>
                    <td>{{ $logRow['context'] }}</td>
                    <td>
                        <span class="brutal-badge {{ $logRow['status'] === 'Failed' ? 'bg-[rgba(220,38,38,0.18)]' : 'bg-[rgba(22,163,74,0.18)]' }}">
                            {{ $logRow['status'] }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="py-6 text-center text-sm text-[var(--color-text-muted)]">
                        Aún no hay actividad registrada.
                    </td>
                </tr>
            @endforelse
        </tbody>
        </table>
    </div>
</div>
