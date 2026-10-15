@props([
    'summary' => [],
])

{{-- Grid de métricas rápidas.
     Acepta un arreglo de tarjetas para reutilizar la misma composición con datos reales luego. --}}
<section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
    @foreach ($summary as $stat)
        @include('dashboard.partials.metric-tile', [
            'label' => $stat['label'],
            'value' => $stat['value'],
            'caption' => $stat['caption'],
            'tone' => $stat['tone'],
        ])
    @endforeach
</section>
