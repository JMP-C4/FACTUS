@props([
    'user',
    'summary' => [],
    'modules' => [],
    'logRows' => [],
])

{{-- Contenedor principal del dashboard de super admin.
     Orquesta parciales más pequeños para mantener esta vista limpia y legible. --}}
<section class="brutal-shell px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto grid max-w-7xl gap-8 xl:grid-cols-[290px_minmax(0,1fr)]">
        @include('dashboard.partials.sidebar', ['user' => $user])

        <div class="space-y-8">
            @include('dashboard.partials.summary-grid', ['summary' => $summary])
            @include('dashboard.partials.modules-grid', ['modules' => $modules])

            <section class="grid gap-5 xl:grid-cols-[1.3fr_0.7fr]">
                @include('dashboard.partials.activity-table', ['logRows' => $logRows])
                @include('dashboard.partials.insights-panel')
            </section>
        </div>
    </div>
</section>
