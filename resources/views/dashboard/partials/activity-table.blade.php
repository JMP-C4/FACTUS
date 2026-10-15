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
                @foreach ($logRows as $row)
                    <tr>
                        <td class="font-semibold">{{ $row['event'] }}</td>
                        <td>{{ $row['owner'] }}</td>
                        <td>{{ $row['context'] }}</td>
                        <td>
                            <span class="brutal-badge bg-[rgba(37,99,235,0.12)]">
                                {{ $row['status'] }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
