@props([
    'user',
    'summary' => [],
    'modules' => [],
    'logRows' => [],
])

{{-- Contenedor principal del dashboard.
     La navegación global vive en layouts/sidebar; aquí sólo se compone un
     bento grid compacto con KPIs, módulos y actividad reciente. --}}
<section class="brutal-shell px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-5">
        @include('dashboard.partials.summary-grid', ['summary' => $summary])
        @include('dashboard.partials.modules-grid', ['modules' => $modules])

        <section class="grid gap-5 lg:grid-cols-3">
            <div class="lg:col-span-2">
                @include('dashboard.partials.activity-table', ['logRows' => $logRows])
            </div>
            <div class="lg:col-span-1">
                @include('dashboard.partials.insights-panel')
            </div>
        </section>
    </div>
</section>
