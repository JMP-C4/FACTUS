@props([
    'summary' => [],
])

{{-- Grid de KPIs del dashboard.
     Bento compacto: hasta 3 columnas en escritorio, apiladas en móvil. --}}
<section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach ($summary as $stat)
        @include('dashboard.partials.metric-tile', [
            'label' => $stat['label'],
            'value' => $stat['value'],
            'caption' => $stat['caption'],
            'tone' => $stat['tone'],
        ])
    @endforeach
</section>
