{{-- Barra de herramientas superior del Diagrama E-R --}}
<div class="mb-4 flex flex-wrap items-center justify-between gap-3 border-[3px] border-black bg-[var(--color-surface)] p-3 shadow-[4px_4px_0_0_#111111]">
    {{-- Filtro de Dominios / Módulos --}}
    <div class="flex flex-wrap items-center gap-1.5">
        <span class="mr-1 text-[11px] font-black uppercase tracking-wider text-[var(--color-text-soft)]">
            Módulo:
        </span>

        <button
            type="button"
            @click="activeDomain = 'all'"
            :class="activeDomain === 'all'
                ? '!bg-black !text-white dark:!bg-white dark:!text-black font-black'
                : 'bg-[var(--color-bg)] text-[var(--color-ink)] hover:border-black'"
            class="cursor-pointer border-[2px] border-black px-2.5 py-1 text-[11px] font-bold uppercase transition"
        >
            Todos ({{ count($tables) }})
        </button>

        @foreach ($modules as $mod)
            <button
                type="button"
                @click="activeDomain = '{{ $mod['name'] }}'"
                :class="activeDomain === '{{ $mod['name'] }}'
                    ? 'ring-2 ring-black dark:ring-white font-black scale-105'
                    : 'opacity-85 hover:opacity-100'"
                class="cursor-pointer border-[2px] border-black px-2.5 py-1 text-[11px] font-bold uppercase transition {{ $mod['theme']['bg'] }} {{ $mod['theme']['text'] }} {{ $mod['theme']['darkBg'] }} {{ $mod['theme']['darkText'] }}"
            >
                {{ $mod['name'] }} ({{ $mod['count'] }})
            </button>
        @endforeach
    </div>

    {{-- Buscador y Controles de Zoom/Vista --}}
    <div class="flex flex-wrap items-center gap-2">
        {{-- Buscador de tablas y columnas --}}
        <div class="relative flex items-center min-w-[210px]">
            <input
                type="text"
                x-model="searchQuery"
                placeholder="Buscar tabla o columna..."
                class="w-full border-[2px] border-black bg-[var(--color-bg)] py-1.5 pl-8 pr-7 text-xs font-medium text-[var(--color-ink)] placeholder-[var(--color-text-soft)] focus:border-black focus:outline-none"
            />
            <svg class="pointer-events-none absolute left-2.5 h-3.5 w-3.5 text-[var(--color-text-soft)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <button
                type="button"
                x-show="searchQuery"
                @click="searchQuery = ''"
                class="absolute right-2 text-xs font-bold text-[var(--color-text-soft)] hover:text-black"
            >
                &times;
            </button>
        </div>

        {{-- Selector rápido de tabla --}}
        <select
            @change="if ($event.target.value) { openTableModal($event.target.value, 'diagram'); $event.target.value = ''; }"
            class="border-[2px] border-black bg-[var(--color-bg)] py-1.5 px-2 text-xs font-bold uppercase text-[var(--color-ink)] focus:border-black focus:outline-none"
        >
            <option value="">Ir a tabla...</option>
            @foreach ($tables as $t)
                <option value="{{ $t['name'] }}">{{ $t['name'] }} ({{ $t['domain'] }})</option>
            @endforeach
        </select>

        {{-- Toggle de Líneas de Relación --}}
        <button
            type="button"
            @click="showRelations = !showRelations"
            :class="showRelations ? 'bg-[var(--color-surface-muted)] text-[var(--color-primary)] font-black' : 'bg-[var(--color-bg)] text-[var(--color-text-soft)]'"
            class="border-[2px] border-black px-2.5 py-1.5 text-xs font-bold uppercase transition hover:border-black"
            title="Mostrar u ocultar líneas de relaciones foráneas"
        >
            <span x-show="showRelations">Líneas: ON</span>
            <span x-show="!showRelations">Líneas: OFF</span>
        </button>

        {{-- Botones de Zoom --}}
        <div class="flex items-center border-[2px] border-black bg-[var(--color-bg)]">
            <button
                type="button"
                @click="zoomOut()"
                class="px-2 py-1 text-sm font-black hover:bg-black hover:text-white"
                title="Alejar (Zoom -)"
            >
                -
            </button>
            <span class="px-2 text-[11px] font-mono font-bold" x-text="Math.round(zoom * 100) + '%'">85%</span>
            <button
                type="button"
                @click="zoomIn()"
                class="px-2 py-1 text-sm font-black hover:bg-black hover:text-white"
                title="Acercar (Zoom +)"
            >
                +
            </button>
            <button
                type="button"
                @click="resetZoom()"
                class="border-l-[2px] border-black px-2 py-1 text-[10px] font-bold uppercase hover:bg-black hover:text-white"
                title="Restablecer zoom y centrar"
            >
                100%
            </button>
        </div>

        {{-- Restablecer Posiciones Guardadas --}}
        <button
            type="button"
            @click="resetPositions()"
            class="border-[2px] border-black bg-[var(--color-bg)] px-2.5 py-1.5 text-[11px] font-bold uppercase transition hover:bg-black hover:text-white"
            title="Restablecer las tarjetas a la disposición inicial por módulos"
        >
            Auto-Ordenar
        </button>

        {{-- Botón de refrescar caché --}}
        <a
            href="{{ route('diagrama-er.index', ['refresh' => 1]) }}"
            class="flex items-center gap-1 border-[2px] border-black bg-[var(--color-surface-muted)] px-2.5 py-1.5 text-[11px] font-bold uppercase text-[var(--color-primary)] transition hover:bg-[var(--color-primary)] hover:text-white"
            title="Refrescar metadatos del esquema desde la base de datos"
        >
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
            </svg>
            Refrescar
        </a>
    </div>
</div>
