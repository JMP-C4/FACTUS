{{-- Modal interactivo de detalle, diagrama y registros de la entidad seleccionada --}}
<div
    x-cloak
    x-show="modalOpen"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6"
    role="dialog"
    aria-modal="true"
    @keydown.escape.window="closeModal()"
>
    {{-- Backdrop oscurecido --}}
    <div
        class="fixed inset-0 bg-black/75 backdrop-blur-sm transition-opacity"
        @click="closeModal()"
    ></div>

    {{-- Contenedor del Modal --}}
    <div
        x-show="modalOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        class="relative flex max-h-[92vh] w-full max-w-6xl flex-col border-[4px] border-black bg-[var(--color-surface)] shadow-[10px_10px_0_0_#111111]"
    >
        {{-- Cabecera del Modal con Metadatos de la Tabla --}}
        <template x-if="currentTable">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b-[3px] border-black bg-[var(--color-surface-muted)] px-5 py-4">
                <div class="flex flex-wrap items-center gap-3">
                    <span
                        class="border-[2px] border-black px-2.5 py-1 text-xs font-black uppercase tracking-wider"
                        :class="currentTable.theme ? (currentTable.theme.bg + ' ' + currentTable.theme.text + ' ' + currentTable.theme.darkBg + ' ' + currentTable.theme.darkText) : 'bg-neutral-200 text-black'"
                        x-text="currentTable.domain"
                    ></span>

                    <h3 class="font-display text-2xl font-black uppercase tracking-tight text-[var(--color-ink)]" x-text="currentTable.name"></h3>

                    <span class="border-[2px] border-black bg-white px-2 py-0.5 text-xs font-bold text-black dark:bg-black dark:text-white" x-text="currentTable.rows_count + ' registros'"></span>
                    <span class="border-[2px] border-black bg-white px-2 py-0.5 text-xs font-bold text-black dark:bg-black dark:text-white" x-text="currentTable.columns_count + ' columnas'"></span>
                    <span class="border-[2px] border-black bg-white px-2 py-0.5 text-xs font-bold text-black dark:bg-black dark:text-white" x-text="'Tamaño: ' + currentTable.size"></span>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Botón para enfocar en el lienzo general --}}
                    <button
                        type="button"
                        @click="focusTableOnCanvas(currentTable.name); closeModal()"
                        class="border-[2px] border-black bg-[var(--color-bg)] px-3 py-1.5 text-xs font-bold uppercase transition hover:bg-black hover:text-white"
                        title="Cerrar modal y enfocar en el lienzo del diagrama general"
                    >
                        🎯 Enfocar en Lienzo
                    </button>

                    {{-- Botón de Cierre --}}
                    <button
                        type="button"
                        @click="closeModal()"
                        class="flex h-9 w-9 items-center justify-center border-[2px] border-black bg-[var(--color-bg)] font-display text-lg font-black transition hover:bg-black hover:text-white"
                        aria-label="Cerrar modal"
                    >
                        ✕
                    </button>
                </div>
            </div>
        </template>

        {{-- Barra de Pestañas con el Diagrama E-R como opción destacada --}}
        <div class="flex flex-wrap items-center gap-1 border-b-[3px] border-black bg-[var(--color-bg)] px-5 pt-3">
            <button
                type="button"
                @click="activeTab = 'diagram'"
                :class="activeTab === 'diagram'
                    ? 'border-b-[4px] border-b-[var(--color-primary)] bg-[var(--color-surface)] text-[var(--color-primary)] font-black'
                    : 'text-[var(--color-text-soft)] hover:text-black dark:hover:text-white'"
                class="border-t-[2px] border-l-[2px] border-r-[2px] border-black px-4 py-2 text-xs font-bold uppercase transition"
            >
                🗺️ Diagrama E-R
            </button>

            <button
                type="button"
                @click="activeTab = 'data'; if (!tableData) fetchTableData(currentTable.name, 1);"
                :class="activeTab === 'data'
                    ? 'border-b-[4px] border-b-[var(--color-primary)] bg-[var(--color-surface)] text-[var(--color-primary)] font-black'
                    : 'text-[var(--color-text-soft)] hover:text-black dark:hover:text-white'"
                class="border-t-[2px] border-l-[2px] border-r-[2px] border-black px-4 py-2 text-xs font-bold uppercase transition"
            >
                📊 Contenido / Registros (<span x-text="currentTable ? currentTable.rows_count : 0"></span>)
            </button>

            <button
                type="button"
                @click="activeTab = 'columns'"
                :class="activeTab === 'columns'
                    ? 'border-b-[4px] border-b-[var(--color-primary)] bg-[var(--color-surface)] text-[var(--color-primary)] font-black'
                    : 'text-[var(--color-text-soft)] hover:text-black dark:hover:text-white'"
                class="border-t-[2px] border-l-[2px] border-r-[2px] border-black px-4 py-2 text-xs font-bold uppercase transition"
            >
                📋 Columnas (<span x-text="currentTable ? currentTable.columns_count : 0"></span>)
            </button>

            <button
                type="button"
                @click="activeTab = 'relations'"
                :class="activeTab === 'relations'
                    ? 'border-b-[4px] border-b-[var(--color-primary)] bg-[var(--color-surface)] text-[var(--color-primary)] font-black'
                    : 'text-[var(--color-text-soft)] hover:text-black dark:hover:text-white'"
                class="border-t-[2px] border-l-[2px] border-r-[2px] border-black px-4 py-2 text-xs font-bold uppercase transition"
            >
                🔗 Relaciones (<span x-text="currentTable ? (currentTable.outgoing_relations.length + currentTable.incoming_relations.length) : 0"></span>)
            </button>

            <button
                type="button"
                @click="activeTab = 'indexes'"
                :class="activeTab === 'indexes'
                    ? 'border-b-[4px] border-b-[var(--color-primary)] bg-[var(--color-surface)] text-[var(--color-primary)] font-black'
                    : 'text-[var(--color-text-soft)] hover:text-black dark:hover:text-white'"
                class="border-t-[2px] border-l-[2px] border-r-[2px] border-black px-4 py-2 text-xs font-bold uppercase transition"
            >
                ⚡ Índices (<span x-text="currentTable ? currentTable.indexes.length : 0"></span>)
            </button>

            <button
                type="button"
                @click="activeTab = 'sql'"
                :class="activeTab === 'sql'
                    ? 'border-b-[4px] border-b-[var(--color-primary)] bg-[var(--color-surface)] text-[var(--color-primary)] font-black'
                    : 'text-[var(--color-text-soft)] hover:text-black dark:hover:text-white'"
                class="border-t-[2px] border-l-[2px] border-r-[2px] border-black px-4 py-2 text-xs font-bold uppercase transition"
            >
                💻 SQL DDL
            </button>
        </div>

        {{-- Cuerpo del Modal (Scrolleable) --}}
        <div class="flex-1 overflow-y-auto p-5">
            {{-- PESTAÑA 1: DIAGRAMA E-R DE LA ENTIDAD SELECCIONADA --}}
            <div x-show="activeTab === 'diagram'" class="space-y-6">
                <template x-if="currentTable">
                    <div>
                        {{-- Explicación rápida --}}
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-2 border-[2px] border-black bg-[var(--color-surface-muted)] p-3 text-xs">
                            <div>
                                <span class="font-bold text-[var(--color-primary)]">Diagrama Relacional Focalizado:</span>
                                <span>Visualiza las tablas padre (que referencia mediante FK) y tablas dependientes (que la referencian). Haz clic en cualquier entidad para navegar hacia ella.</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="border border-black bg-white px-2 py-0.5 text-[10px] font-bold dark:bg-black dark:text-white" x-text="currentTable.outgoing_relations.length + ' Padres (Belongs To)'"></span>
                                <span class="border border-black bg-white px-2 py-0.5 text-[10px] font-bold dark:bg-black dark:text-white" x-text="currentTable.incoming_relations.length + ' Hijas (Has Many)'"></span>
                            </div>
                        </div>

                        {{-- Lienzo Visual del Diagrama de la Entidad --}}
                        <div class="relative overflow-x-auto border-[3px] border-black bg-[var(--color-bg)] p-6 shadow-[4px_4px_0_0_#111111]"
                             style="background-image: radial-gradient(circle, rgba(0,0,0,0.12) 1.5px, transparent 1.5px); background-size: 20px 20px;">
                            
                            <div class="mx-auto flex min-w-[760px] items-center justify-between gap-6">
                                {{-- COLUMNA IZQUIERDA: Tablas Padre (Belongs To) --}}
                                <div class="flex w-[260px] flex-col gap-3">
                                    <div class="flex items-center justify-between border-b-[2px] border-black pb-1">
                                        <span class="font-display text-[11px] font-black uppercase text-[var(--color-text-soft)]">
                                            ← Tablas Padre (Belongs To)
                                        </span>
                                        <span class="border border-black bg-white px-1.5 text-[10px] font-bold dark:bg-black" x-text="currentTable.outgoing_relations.length"></span>
                                    </div>

                                    <template x-if="currentTable.outgoing_relations.length === 0">
                                        <div class="border border-dashed border-black/40 p-3 text-center text-[11px] text-[var(--color-text-soft)]">
                                            No depende de tablas padre
                                        </div>
                                    </template>

                                    <template x-for="rel in currentTable.outgoing_relations" :key="rel.id">
                                        <div
                                            @click="openTableModal(rel.target_table, 'diagram')"
                                            class="group cursor-pointer border-[2px] border-black bg-[var(--color-surface)] p-2.5 shadow-[3px_3px_0_0_#111111] transition hover:-translate-y-0.5 hover:shadow-[5px_5px_0_0_#2563eb]"
                                        >
                                            <div class="flex items-center justify-between">
                                                <span class="font-display text-xs font-black uppercase text-[var(--color-ink)] group-hover:text-[var(--color-primary)]" x-text="rel.target_table"></span>
                                                <span class="border border-black bg-blue-100 px-1 text-[9px] font-bold text-blue-900 dark:bg-blue-950 dark:text-blue-200">1</span>
                                            </div>
                                            <div class="mt-1 font-mono text-[10px] text-[var(--color-text-soft)]">
                                                <span class="text-[var(--color-primary)]" x-text="rel.source_columns.join(', ')"></span>
                                                <span>&rarr;</span>
                                                <span x-text="rel.target_columns.join(', ')"></span>
                                            </div>
                                            <div class="mt-1 flex items-center justify-between text-[9px] font-bold uppercase text-[var(--color-text-soft)]">
                                                <span x-text="'ON DEL: ' + rel.on_delete"></span>
                                                <span class="text-[var(--color-primary)] group-hover:underline">Explorar &raquo;</span>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                {{-- COLUMNA CENTRAL: Entidad Actual Destacada --}}
                                <div class="w-[300px] shrink-0 border-[4px] border-black bg-[var(--color-surface)] shadow-[8px_8px_0_0_#111111]">
                                    {{-- Cabecera de la Entidad Central --}}
                                    <div class="border-b-[3px] border-black p-3 text-center" :class="currentTable.theme ? currentTable.theme.bg : 'bg-neutral-200'">
                                        <span class="border border-black bg-black px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-white dark:bg-white dark:text-black">
                                            Entidad Seleccionada
                                        </span>
                                        <h4 class="mt-2 font-display text-lg font-black uppercase tracking-tight text-[var(--color-ink)]" x-text="currentTable.name"></h4>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-[var(--color-text-soft)]" x-text="currentTable.domain + ' • #' + currentTable.rows_count + ' registros'"></p>
                                    </div>

                                    {{-- Columnas Clave de la Entidad --}}
                                    <div class="max-h-[260px] overflow-y-auto divide-y divide-black/10 p-2 font-mono text-xs">
                                        <template x-for="col in currentTable.columns" :key="col.name">
                                            <div class="flex items-center justify-between py-1 px-1.5" :class="col.is_primary ? 'bg-amber-100/60 dark:bg-amber-950/40 font-bold' : (col.is_foreign ? 'bg-blue-100/60 dark:bg-blue-950/40' : '')">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <span x-show="col.is_primary" class="border border-black bg-amber-400 px-1 text-[8px] font-black text-black">PK</span>
                                                    <span x-show="col.is_foreign" class="border border-black bg-blue-400 px-1 text-[8px] font-black text-black">FK</span>
                                                    <span class="truncate" x-text="col.name"></span>
                                                </div>
                                                <span class="text-[10px] text-[var(--color-text-soft)]" x-text="col.type_short"></span>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- Botón rápido para ver registros --}}
                                    <div class="border-t-[3px] border-black bg-[var(--color-surface-muted)] p-2 text-center">
                                        <button
                                            type="button"
                                            @click="activeTab = 'data'; if (!tableData) fetchTableData(currentTable.name, 1);"
                                            class="w-full border-[2px] border-black bg-black py-1 text-xs font-bold uppercase text-white hover:bg-neutral-800 dark:bg-white dark:text-black"
                                        >
                                            Ver Registros de Esta Tabla &raquo;
                                        </button>
                                    </div>
                                </div>

                                {{-- COLUMNA DERECHA: Tablas Dependientes (Has Many) --}}
                                <div class="flex w-[260px] flex-col gap-3">
                                    <div class="flex items-center justify-between border-b-[2px] border-black pb-1">
                                        <span class="font-display text-[11px] font-black uppercase text-[var(--color-text-soft)]">
                                            Tablas Hijas (Has Many) &rarr;
                                        </span>
                                        <span class="border border-black bg-white px-1.5 text-[10px] font-bold dark:bg-black" x-text="currentTable.incoming_relations.length"></span>
                                    </div>

                                    <template x-if="currentTable.incoming_relations.length === 0">
                                        <div class="border border-dashed border-black/40 p-3 text-center text-[11px] text-[var(--color-text-soft)]">
                                            Ninguna tabla externa depende de esta entidad
                                        </div>
                                    </template>

                                    <template x-for="rel in currentTable.incoming_relations" :key="rel.id">
                                        <div
                                            @click="openTableModal(rel.source_table, 'diagram')"
                                            class="group cursor-pointer border-[2px] border-black bg-[var(--color-surface)] p-2.5 shadow-[3px_3px_0_0_#111111] transition hover:-translate-y-0.5 hover:shadow-[5px_5px_0_0_#10b981]"
                                        >
                                            <div class="flex items-center justify-between">
                                                <span class="font-display text-xs font-black uppercase text-[var(--color-ink)] group-hover:text-emerald-600" x-text="rel.source_table"></span>
                                                <span class="border border-black bg-emerald-100 px-1 text-[9px] font-bold text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">N</span>
                                            </div>
                                            <div class="mt-1 font-mono text-[10px] text-[var(--color-text-soft)]">
                                                <span class="text-emerald-600 dark:text-emerald-400" x-text="rel.source_table + '.' + rel.source_columns.join(', ')"></span>
                                                <span>&rarr;</span>
                                                <span x-text="rel.target_columns.join(', ')"></span>
                                            </div>
                                            <div class="mt-1 flex items-center justify-between text-[9px] font-bold uppercase text-[var(--color-text-soft)]">
                                                <span x-text="'ON DEL: ' + rel.on_delete"></span>
                                                <span class="text-emerald-600 group-hover:underline">Explorar &raquo;</span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- PESTAÑA 2: CONTENIDO / REGISTROS REALES --}}
            <div x-show="activeTab === 'data'" class="space-y-4">
                <template x-if="currentTable">
                    <div>
                        {{-- Controles de la tabla de datos --}}
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-3 border-[2px] border-black bg-[var(--color-bg)] p-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <div class="relative min-w-[240px]">
                                    <input
                                        type="text"
                                        x-model="modalSearch"
                                        @keydown.enter="fetchTableData(currentTable.name, 1)"
                                        placeholder="Filtrar datos en columnas de texto..."
                                        class="w-full border-[2px] border-black bg-[var(--color-surface)] py-1.5 pl-8 pr-7 text-xs font-medium text-[var(--color-ink)] focus:border-black focus:outline-none"
                                    />
                                    <svg class="pointer-events-none absolute left-2.5 top-2.5 h-3.5 w-3.5 text-[var(--color-text-soft)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>

                                <button
                                    type="button"
                                    @click="fetchTableData(currentTable.name, 1)"
                                    class="border-[2px] border-black bg-black px-3 py-1.5 text-xs font-bold uppercase text-white hover:bg-neutral-800 dark:bg-white dark:text-black"
                                >
                                    Filtrar
                                </button>

                                <button
                                    type="button"
                                    x-show="modalSearch"
                                    @click="modalSearch = ''; fetchTableData(currentTable.name, 1)"
                                    class="border-[2px] border-black bg-[var(--color-surface)] px-2.5 py-1.5 text-xs font-bold uppercase hover:bg-black hover:text-white"
                                >
                                    Limpiar
                                </button>
                            </div>

                            {{-- Paginación y Selector por página --}}
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-1 text-xs">
                                    <span class="font-bold">Filas:</span>
                                    <select
                                        x-model="modalPerPage"
                                        @change="fetchTableData(currentTable.name, 1)"
                                        class="border-[2px] border-black bg-[var(--color-surface)] px-2 py-1 text-xs font-bold"
                                    >
                                        <option value="10">10</option>
                                        <option value="15">15</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                    </select>
                                </div>

                                <span class="text-xs font-mono font-bold" x-text="'Pág. ' + (tableData ? tableData.current_page : 1) + ' de ' + (tableData ? tableData.last_page : 1)"></span>

                                <div class="flex border-[2px] border-black">
                                    <button
                                        type="button"
                                        @click="if (tableData && tableData.current_page > 1) fetchTableData(currentTable.name, tableData.current_page - 1)"
                                        :disabled="!tableData || tableData.current_page <= 1"
                                        class="px-2.5 py-1 text-xs font-black disabled:opacity-30 hover:bg-black hover:text-white"
                                    >
                                        &laquo; Ant
                                    </button>
                                    <button
                                        type="button"
                                        @click="if (tableData && tableData.has_more) fetchTableData(currentTable.name, tableData.current_page + 1)"
                                        :disabled="!tableData || !tableData.has_more"
                                        class="border-l-[2px] border-black px-2.5 py-1 text-xs font-black disabled:opacity-30 hover:bg-black hover:text-white"
                                    >
                                        Sig &raquo;
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Aviso de seguridad --}}
                        <div class="mb-3 flex items-center gap-2 border-[2px] border-black bg-[rgba(37,99,235,0.08)] px-3 py-2 text-xs font-medium text-[var(--color-ink)]">
                            <span class="font-bold text-[var(--color-primary)]">🛡️ Privacidad:</span>
                            <span>Contraseñas, hashes y tokens de acceso son enmascarados automáticamente para resguardo del sistema.</span>
                        </div>

                        {{-- Estado de Carga --}}
                        <div x-show="dataLoading" class="flex flex-col items-center justify-center py-12">
                            <div class="h-8 w-8 animate-spin border-4 border-black border-t-transparent dark:border-white dark:border-t-transparent"></div>
                            <p class="mt-3 font-display text-xs font-bold uppercase tracking-wider text-[var(--color-text-soft)]">Cargando registros...</p>
                        </div>

                        {{-- Tabla de Registros --}}
                        <template x-if="!dataLoading && modalColumns.length > 0 && modalRows.length > 0">
                            <div class="overflow-x-auto border-[3px] border-black bg-[var(--color-surface)]">
                                <table class="w-full text-left text-xs">
                                    <thead class="border-b-[3px] border-black bg-[var(--color-surface-muted)] text-[var(--color-ink)]">
                                        <tr>
                                            <template x-for="col in modalColumns" :key="col.name">
                                                <th class="border-r-[2px] border-black px-3 py-2.5 font-display font-black uppercase tracking-wider">
                                                    <div class="flex items-center gap-1.5">
                                                        <span x-text="col.name"></span>
                                                        <span x-show="col.is_primary" class="border border-black bg-amber-300 px-1 text-[9px] font-black text-black">PK</span>
                                                        <span x-show="col.is_foreign" class="border border-black bg-blue-300 px-1 text-[9px] font-black text-black">FK</span>
                                                    </div>
                                                </th>
                                            </template>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y-[2px] divide-black font-mono">
                                        <template x-for="(row, rowIndex) in modalRows" :key="rowIndex">
                                            <tr class="hover:bg-[var(--color-bg)]">
                                                <template x-for="col in modalColumns" :key="col.name">
                                                    <td class="max-w-[260px] truncate border-r-[2px] border-black px-3 py-2 text-[11px]">
                                                        <template x-if="row[col.name] === null">
                                                            <span class="border border-black/40 bg-neutral-200 px-1.5 py-0.5 text-[9px] font-bold text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400">NULL</span>
                                                        </template>
                                                        <template x-if="row[col.name] !== null && typeof row[col.name] === 'boolean'">
                                                            <span
                                                                class="border border-black px-1.5 py-0.5 text-[9px] font-bold"
                                                                :class="row[col.name] ? 'bg-emerald-300 text-black' : 'bg-rose-300 text-black'"
                                                                x-text="row[col.name] ? 'TRUE' : 'FALSE'"
                                                            ></span>
                                                        </template>
                                                        <template x-if="row[col.name] !== null && typeof row[col.name] !== 'boolean'">
                                                            <span :title="String(row[col.name])" x-text="String(row[col.name])"></span>
                                                        </template>
                                                    </td>
                                                </template>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        {{-- Estado Vacío --}}
                        <template x-if="!dataLoading && modalRows.length === 0">
                            <div class="border-[3px] border-dashed border-black p-8 text-center bg-[var(--color-bg)]">
                                <p class="font-display text-sm font-bold uppercase text-[var(--color-ink)]">Esta tabla no contiene registros actualmente</p>
                                <p class="mt-1 text-xs text-[var(--color-text-soft)]">Puedes crear registros desde los módulos correspondientes o revisar la estructura de columnas.</p>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- PESTAÑA 3: ESTRUCTURA DE COLUMNAS --}}
            <div x-show="activeTab === 'columns'" class="space-y-4">
                <template x-if="currentTable && currentTable.columns">
                    <div class="overflow-x-auto border-[3px] border-black bg-[var(--color-surface)]">
                        <table class="w-full text-left text-xs">
                            <thead class="border-b-[3px] border-black bg-[var(--color-surface-muted)] text-[var(--color-ink)] font-display font-black uppercase">
                                <tr>
                                    <th class="border-r-[2px] border-black px-4 py-2.5">Columna</th>
                                    <th class="border-r-[2px] border-black px-4 py-2.5">Tipo de Dato</th>
                                    <th class="border-r-[2px] border-black px-4 py-2.5">Atributos / Claves</th>
                                    <th class="border-r-[2px] border-black px-4 py-2.5">Nulo</th>
                                    <th class="border-r-[2px] border-black px-4 py-2.5">Valor por Defecto</th>
                                    <th class="px-4 py-2.5">Relación Externa</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-[2px] divide-black font-mono">
                                <template x-for="col in currentTable.columns" :key="col.name">
                                    <tr class="hover:bg-[var(--color-bg)]">
                                        <td class="border-r-[2px] border-black px-4 py-2 font-bold text-[var(--color-ink)]" x-text="col.name"></td>
                                        <td class="border-r-[2px] border-black px-4 py-2 text-[var(--color-primary)] font-bold" x-text="col.type"></td>
                                        <td class="border-r-[2px] border-black px-4 py-2">
                                            <div class="flex flex-wrap gap-1">
                                                <span x-show="col.is_primary" class="border-[2px] border-black bg-amber-400 px-1.5 py-0.2 text-[10px] font-black text-black">PK</span>
                                                <span x-show="col.is_foreign" class="border-[2px] border-black bg-blue-400 px-1.5 py-0.2 text-[10px] font-black text-black">FK</span>
                                                <span x-show="col.auto_increment" class="border border-black bg-neutral-200 px-1 py-0.2 text-[9px] font-bold text-black">AUTO</span>
                                            </div>
                                        </td>
                                        <td class="border-r-[2px] border-black px-4 py-2">
                                            <span
                                                class="border border-black px-1.5 py-0.5 text-[10px] font-bold"
                                                :class="col.nullable ? 'bg-neutral-200 text-black dark:bg-neutral-800 dark:text-neutral-300' : 'bg-black text-white dark:bg-white dark:text-black'"
                                                x-text="col.nullable ? 'NULL' : 'NOT NULL'"
                                            ></span>
                                        </td>
                                        <td class="border-r-[2px] border-black px-4 py-2 text-[var(--color-text-soft)]" x-text="col.default !== null ? col.default : '—'"></td>
                                        <td class="px-4 py-2">
                                            <template x-if="col.is_foreign && col.foreign_target">
                                                <button
                                                    type="button"
                                                    @click="openTableModal(col.foreign_target.foreign_table, 'diagram')"
                                                    class="inline-flex items-center gap-1 border-[2px] border-black bg-blue-100 px-2 py-0.5 text-[11px] font-bold text-blue-900 transition hover:bg-blue-600 hover:text-white dark:bg-blue-950 dark:text-blue-200"
                                                >
                                                    <span>&rarr;</span>
                                                    <span x-text="col.foreign_target.foreign_table + '.' + col.foreign_target.foreign_column"></span>
                                                </button>
                                            </template>
                                            <template x-if="!col.is_foreign">
                                                <span class="text-[var(--color-text-soft)]">—</span>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>
            </div>

            {{-- PESTAÑA 4: LISTA DETALLADA DE RELACIONES --}}
            <div x-show="activeTab === 'relations'" class="space-y-6">
                <template x-if="currentTable">
                    <div class="grid gap-6 md:grid-cols-2">
                        {{-- Relaciones Salientes (Belongs To) --}}
                        <div class="border-[3px] border-black bg-[var(--color-surface)] p-4 shadow-[4px_4px_0_0_#111111]">
                            <div class="mb-3 flex items-center justify-between border-b-[2px] border-black pb-2">
                                <h4 class="font-display text-sm font-black uppercase text-[var(--color-ink)]">
                                    Relaciones Salientes (Belongs To)
                                </h4>
                                <span class="border border-black bg-[var(--color-bg)] px-2 py-0.5 text-xs font-bold" x-text="currentTable.outgoing_relations.length"></span>
                            </div>
                            <p class="mb-3 text-xs text-[var(--color-text-soft)]">
                                Tablas a las que <strong class="text-[var(--color-ink)]" x-text="currentTable.name"></strong> hace referencia mediante clave foránea:
                            </p>

                            <template x-if="currentTable.outgoing_relations.length === 0">
                                <div class="border border-dashed border-black/50 p-4 text-center text-xs text-[var(--color-text-soft)]">
                                    No posee claves foráneas salientes hacia otras entidades.
                                </div>
                            </template>

                            <div class="space-y-2">
                                <template x-for="rel in currentTable.outgoing_relations" :key="rel.id">
                                    <div class="flex items-center justify-between border-[2px] border-black bg-[var(--color-bg)] p-2.5">
                                        <div>
                                            <div class="flex items-center gap-1.5 font-mono text-xs font-bold">
                                                <span class="text-[var(--color-primary)]" x-text="rel.source_columns.join(', ')"></span>
                                                <span class="text-neutral-500">&rarr;</span>
                                                <span class="text-emerald-600 dark:text-emerald-400" x-text="rel.target_table + '.' + rel.target_columns.join(', ')"></span>
                                            </div>
                                            <p class="mt-0.5 text-[10px] text-[var(--color-text-soft)]" x-text="'ON DELETE ' + rel.on_delete.toUpperCase() + ' • ' + rel.name"></p>
                                        </div>

                                        <button
                                            type="button"
                                            @click="openTableModal(rel.target_table, 'diagram')"
                                            class="border-[2px] border-black bg-white px-2 py-1 text-[10px] font-bold uppercase transition hover:bg-black hover:text-white dark:bg-black dark:text-white"
                                        >
                                            Ver Diagrama &raquo;
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Relaciones Entrantes (Has Many / Referenciada por) --}}
                        <div class="border-[3px] border-black bg-[var(--color-surface)] p-4 shadow-[4px_4px_0_0_#111111]">
                            <div class="mb-3 flex items-center justify-between border-b-[2px] border-black pb-2">
                                <h4 class="font-display text-sm font-black uppercase text-[var(--color-ink)]">
                                    Relaciones Entrantes (Referenciada por)
                                </h4>
                                <span class="border border-black bg-[var(--color-bg)] px-2 py-0.5 text-xs font-bold" x-text="currentTable.incoming_relations.length"></span>
                            </div>
                            <p class="mb-3 text-xs text-[var(--color-text-soft)]">
                                Tablas que dependen de <strong class="text-[var(--color-ink)]" x-text="currentTable.name"></strong> o apuntan a sus registros:
                            </p>

                            <template x-if="currentTable.incoming_relations.length === 0">
                                <div class="border border-dashed border-black/50 p-4 text-center text-xs text-[var(--color-text-soft)]">
                                    Ninguna tabla externa declara una clave foránea hacia esta entidad.
                                </div>
                            </template>

                            <div class="space-y-2">
                                <template x-for="rel in currentTable.incoming_relations" :key="rel.id">
                                    <div class="flex items-center justify-between border-[2px] border-black bg-[var(--color-bg)] p-2.5">
                                        <div>
                                            <div class="flex items-center gap-1.5 font-mono text-xs font-bold">
                                                <span class="text-emerald-600 dark:text-emerald-400" x-text="rel.source_table + '.' + rel.source_columns.join(', ')"></span>
                                                <span class="text-neutral-500">&rarr;</span>
                                                <span class="text-[var(--color-primary)]" x-text="rel.target_columns.join(', ')"></span>
                                            </div>
                                            <p class="mt-0.5 text-[10px] text-[var(--color-text-soft)]" x-text="'ON DELETE ' + rel.on_delete.toUpperCase() + ' • ' + rel.name"></p>
                                        </div>

                                        <button
                                            type="button"
                                            @click="openTableModal(rel.source_table, 'diagram')"
                                            class="border-[2px] border-black bg-white px-2 py-1 text-[10px] font-bold uppercase transition hover:bg-black hover:text-white dark:bg-black dark:text-white"
                                        >
                                            Ver Diagrama &raquo;
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- PESTAÑA 5: ÍNDICES & LLAVES --}}
            <div x-show="activeTab === 'indexes'" class="space-y-4">
                <template x-if="currentTable && currentTable.indexes">
                    <div class="overflow-x-auto border-[3px] border-black bg-[var(--color-surface)]">
                        <table class="w-full text-left text-xs">
                            <thead class="border-b-[3px] border-black bg-[var(--color-surface-muted)] text-[var(--color-ink)] font-display font-black uppercase">
                                <tr>
                                    <th class="border-r-[2px] border-black px-4 py-2.5">Nombre del Índice</th>
                                    <th class="border-r-[2px] border-black px-4 py-2.5">Columnas</th>
                                    <th class="border-r-[2px] border-black px-4 py-2.5">Tipo</th>
                                    <th class="border-r-[2px] border-black px-4 py-2.5">Único</th>
                                    <th class="px-4 py-2.5">Clave Primaria</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-[2px] divide-black font-mono">
                                <template x-for="idx in currentTable.indexes" :key="idx.name">
                                    <tr class="hover:bg-[var(--color-bg)]">
                                        <td class="border-r-[2px] border-black px-4 py-2 font-bold" x-text="idx.name"></td>
                                        <td class="border-r-[2px] border-black px-4 py-2 text-[var(--color-primary)] font-bold" x-text="idx.columns.join(', ')"></td>
                                        <td class="border-r-[2px] border-black px-4 py-2 uppercase" x-text="idx.type"></td>
                                        <td class="border-r-[2px] border-black px-4 py-2">
                                            <span
                                                class="border border-black px-1.5 py-0.5 text-[9px] font-bold"
                                                :class="idx.unique ? 'bg-amber-300 text-black' : 'bg-neutral-200 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400'"
                                                x-text="idx.unique ? 'SÍ (UNIQUE)' : 'NO'"
                                            ></span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <span
                                                class="border border-black px-1.5 py-0.5 text-[9px] font-bold"
                                                :class="idx.primary ? 'bg-amber-400 text-black font-black' : 'bg-neutral-200 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400'"
                                                x-text="idx.primary ? 'PRIMARY KEY' : 'NO'"
                                            ></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>
            </div>

            {{-- PESTAÑA 6: SQL DDL --}}
            <div x-show="activeTab === 'sql'" class="space-y-4">
                <template x-if="currentTable">
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-xs font-bold text-[var(--color-text-soft)]">Definición SQL generada para esta entidad:</span>
                            <button
                                type="button"
                                @click="copySql()"
                                class="border-[2px] border-black bg-[var(--color-surface-muted)] px-3 py-1 text-xs font-bold uppercase transition hover:bg-black hover:text-white"
                            >
                                <span x-text="copiedSql ? '✓ ¡Copiado!' : '📋 Copiar SQL'"></span>
                            </button>
                        </div>
                        <pre class="overflow-x-auto border-[3px] border-black bg-neutral-900 p-4 font-mono text-xs leading-relaxed text-emerald-400"><code x-text="generateSql(currentTable)"></code></pre>
                    </div>
                </template>
            </div>
        </div>

        {{-- Pie del modal --}}
        <div class="flex items-center justify-between border-t-[3px] border-black bg-[var(--color-surface-muted)] px-5 py-3 text-xs">
            <span class="text-[var(--color-text-soft)]">
                FACTUS OS • Administrador de Base de Datos • Driver: <strong class="text-[var(--color-ink)]" x-text="stats.database_driver"></strong>
            </span>
            <button
                type="button"
                @click="closeModal()"
                class="border-[2px] border-black bg-black px-4 py-1.5 font-bold uppercase text-white hover:bg-neutral-800 dark:bg-white dark:text-black"
            >
                Cerrar
            </button>
        </div>
    </div>
</div>
