@props([
    'modules' => [],
])

{{-- Bento de módulos funcionales.
     Rejilla densa y homogénea para escanear rápido el estado de cada área. --}}
<section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    @foreach ($modules as $module)
        @include('dashboard.partials.module-card', [
            'title' => $module['title'],
            'description' => $module['description'],
            'metric' => $module['metric'],
            'status' => $module['status'],
            'tone' => $module['tone'],
            'url' => $module['url'] ?? null,
        ])
    @endforeach
</section>
