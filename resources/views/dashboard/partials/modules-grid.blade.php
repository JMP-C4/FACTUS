@props([
    'modules' => [],
])

{{-- Grid de módulos funcionales del super admin.
     Cada bloque se pinta desde el arreglo recibido para no hardcodear el diseño en el dashboard raíz. --}}
<section class="grid gap-5 lg:grid-cols-2">
    @foreach ($modules as $module)
        <div id="{{ $module['id'] }}" class="{{ $module['span'] ?? '' }}">
            @include('dashboard.partials.module-card', [
                'title' => $module['title'],
                'description' => $module['description'],
                'metric' => $module['metric'],
                'status' => $module['status'],
                'tone' => $module['tone'],
            ])
        </div>
    @endforeach
</section>
