<x-app-layout>
    {{-- Definición temprana del componente Alpine erDiagram antes de cualquier elemento del DOM --}}
    <script>
        (function() {
            function createErDiagram({ tablesData, relationshipsData, modulesData, statsData }) {
                return {
                    tables: tablesData || [],
                    relationships: relationshipsData || [],
                    modules: modulesData || [],
                    stats: statsData || {},

                    // Estado de Visualización
                    activeDomain: 'all',
                    searchQuery: '',
                    showRelations: true,
                    hoveredTable: null,

                    // Pan & Zoom
                    zoom: 0.85,
                    panX: 40,
                    panY: 30,
                    isPanning: false,
                    panStartX: 0,
                    panStartY: 0,

                    // Arrastre de Nodos
                    draggedTable: null,
                    dragStartX: 0,
                    dragStartY: 0,
                    nodeInitialX: 0,
                    nodeInitialY: 0,

                    // Estado del Modal
                    modalOpen: false,
                    selectedTableName: null,
                    activeTab: 'diagram', // 'diagram', 'data', 'columns', 'relations', 'indexes', 'sql'
                    tableData: {
                        columns: [],
                        data: [],
                        total: 0,
                        current_page: 1,
                        last_page: 1,
                        has_more: false,
                    },
                    dataLoading: false,
                    modalSearch: '',
                    modalPerPage: 15,
                    copiedSql: false,

                    get modalColumns() {
                        return (this.tableData && Array.isArray(this.tableData.columns)) ? this.tableData.columns : [];
                    },

                    get modalRows() {
                        return (this.tableData && Array.isArray(this.tableData.data)) ? this.tableData.data : [];
                    },

                    get currentTable() {
                        if (!this.selectedTableName) {
                            return (this.tables && this.tables.length > 0) ? this.tables[0] : null;
                        }
                        return this.tables.find(t => t.name === this.selectedTableName) || (this.tables.length > 0 ? this.tables[0] : null);
                    },

                    get visibleRelationships() {
                        return this.relationships.filter(rel => {
                            const source = this.tables.find(t => t.name === rel.source_table);
                            const target = this.tables.find(t => t.name === rel.target_table);
                            if (!source || !target) return false;

                            if (this.activeDomain === 'all') return true;
                            return source.domain === this.activeDomain || target.domain === this.activeDomain;
                        });
                    },

                    // Generación pura y libre de errores de SVG para conectores Bézier
                    get svgContent() {
                        if (!this.showRelations) return '';

                        const defs = `
                            <defs>
                                <marker id="er-arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                                    <path d="M 0 1 L 10 5 L 0 9 z" fill="#111111" />
                                </marker>
                                <marker id="er-arrow-active" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
                                    <path d="M 0 1 L 10 5 L 0 9 z" fill="#2563eb" />
                                </marker>
                            </defs>
                        `;

                        const paths = this.visibleRelationships.map(rel => {
                            const d = this.getRelationPath(rel);
                            if (!d) return '';
                            const active = this.isRelationActive(rel);
                            const dimmed = this.isTableDimmed(rel.source_table) || this.isTableDimmed(rel.target_table);
                            const strokeColor = active ? '#2563eb' : (dimmed ? 'rgba(150,150,150,0.18)' : (rel.color || '#111111'));
                            const strokeWidth = active ? '3.5' : '2';
                            const marker = active ? 'url(#er-arrow-active)' : 'url(#er-arrow)';
                            return `<path d="${d}" stroke="${strokeColor}" stroke-width="${strokeWidth}" fill="none" marker-end="${marker}" />`;
                        }).join('\n');

                        return defs + paths;
                    },

                    initDiagram() {
                        try {
                            const saved = localStorage.getItem('factus.er_diagram.positions_v3');
                            if (saved) {
                                const parsed = JSON.parse(saved);
                                this.tables.forEach(table => {
                                    if (parsed[table.name]) {
                                        table.position.x = parsed[table.name].x;
                                        table.position.y = parsed[table.name].y;
                                    }
                                });
                            }
                        } catch (e) {}
                    },

                    // Zoom y Pan
                    zoomIn() {
                        this.zoom = Math.min(1.8, Number((this.zoom + 0.15).toFixed(2)));
                    },
                    zoomOut() {
                        this.zoom = Math.max(0.35, Number((this.zoom - 0.15).toFixed(2)));
                    },
                    resetZoom() {
                        this.zoom = 0.85;
                        this.panX = 40;
                        this.panY = 30;
                    },
                    onWheel(event) {
                        const delta = event.deltaY > 0 ? -0.07 : 0.07;
                        this.zoom = Math.min(1.8, Math.max(0.35, Number((this.zoom + delta).toFixed(2))));
                    },
                    startPan(event) {
                        if (event.target.id === 'diagram-viewport' || event.target.id === 'diagram-canvas' || event.target.tagName === 'svg') {
                            this.isPanning = true;
                            this.panStartX = event.clientX - this.panX;
                            this.panStartY = event.clientY - this.panY;
                        }
                    },
                    startDragNode(event, table) {
                        this.draggedTable = table;
                        this.dragStartX = event.clientX;
                        this.dragStartY = event.clientY;
                        this.nodeInitialX = table.position.x;
                        this.nodeInitialY = table.position.y;
                    },
                    onMouseMove(event) {
                        if (this.isPanning) {
                            this.panX = event.clientX - this.panStartX;
                            this.panY = event.clientY - this.panStartY;
                        } else if (this.draggedTable) {
                            const deltaX = (event.clientX - this.dragStartX) / this.zoom;
                            const deltaY = (event.clientY - this.dragStartY) / this.zoom;
                            this.draggedTable.position.x = Math.max(20, Math.round(this.nodeInitialX + deltaX));
                            this.draggedTable.position.y = Math.max(20, Math.round(this.nodeInitialY + deltaY));
                        }
                    },
                    endDragOrPan() {
                        if (this.draggedTable) {
                            this.savePositions();
                            this.draggedTable = null;
                        }
                        this.isPanning = false;
                    },
                    savePositions() {
                        try {
                            const positions = {};
                            this.tables.forEach(t => {
                                positions[t.name] = { x: t.position.x, y: t.position.y };
                            });
                            localStorage.setItem('factus.er_diagram.positions_v3', JSON.stringify(positions));
                        } catch (e) {}
                    },
                    resetPositions() {
                        localStorage.removeItem('factus.er_diagram.positions_v1');
                        localStorage.removeItem('factus.er_diagram.positions_v2');
                        localStorage.removeItem('factus.er_diagram.positions_v3');
                        window.location.reload();
                    },

                    // Visibilidad y Resaltado
                    isTableVisible(table) {
                        if (this.activeDomain !== 'all' && table.domain !== this.activeDomain) {
                            return false;
                        }
                        return true;
                    },
                    isTableHighlighted(table) {
                        if (!this.searchQuery) return false;
                        const query = this.searchQuery.toLowerCase().trim();
                        if (table.name.toLowerCase().includes(query)) return true;
                        return table.columns.some(c => c.name.toLowerCase().includes(query));
                    },
                    isTableDimmed(tableName) {
                        if (!this.hoveredTable) return false;
                        if (this.hoveredTable === tableName) return false;

                        const isConnected = this.relationships.some(rel =>
                            (rel.source_table === this.hoveredTable && rel.target_table === tableName) ||
                            (rel.target_table === this.hoveredTable && rel.source_table === tableName)
                        );
                        return !isConnected;
                    },
                    isRelationActive(rel) {
                        if (!this.hoveredTable) return false;
                        return rel.source_table === this.hoveredTable || rel.target_table === this.hoveredTable;
                    },

                    // Cálculo Dinámico de Conectores Bézier
                    getRelationPath(rel) {
                        const source = this.tables.find(t => t.name === rel.source_table);
                        const target = this.tables.find(t => t.name === rel.target_table);
                        if (!source || !target) return '';

                        const w = 290;
                        const h = 180;

                        const sX = source.position.x;
                        const sY = source.position.y;
                        const tX = target.position.x;
                        const tY = target.position.y;

                        let startX, startY, endX, endY;

                        if (tX > sX + w + 30) {
                            startX = sX + w;
                            startY = sY + 45;
                            endX = tX;
                            endY = tY + 45;
                        } else if (tX + w + 30 < sX) {
                            startX = sX;
                            startY = sY + 45;
                            endX = tX + w;
                            endY = tY + 45;
                        } else if (tY > sY + h) {
                            startX = sX + w / 2;
                            startY = sY + h;
                            endX = tX + w / 2;
                            endY = tY;
                        } else {
                            startX = sX + w / 2;
                            startY = sY;
                            endX = tX + w / 2;
                            endY = tY + h;
                        }

                        const dx = Math.max(25, Math.abs(endX - startX) * 0.45);
                        return 'M ' + startX + ' ' + startY + ' C ' + (startX + (endX > startX ? dx : -dx)) + ' ' + startY + ', ' + (endX - (endX > startX ? dx : -dx)) + ' ' + endY + ', ' + endX + ' ' + endY;
                    },

                    // Modal
                    openTableModal(tableName, initialTab = 'diagram') {
                        this.selectedTableName = tableName;
                        this.activeTab = initialTab;
                        this.modalOpen = true;
                        this.modalSearch = '';
                        if (initialTab === 'data') {
                            this.fetchTableData(tableName, 1);
                        }
                    },
                    closeModal() {
                        this.modalOpen = false;
                        this.tableData = {
                            columns: [],
                            data: [],
                            total: 0,
                            current_page: 1,
                            last_page: 1,
                            has_more: false,
                        };
                    },
                    async fetchTableData(tableName, page = 1) {
                        this.dataLoading = true;
                        try {
                            const url = new URL('{{ route('diagrama-er.table.data', ['table' => ':table']) }}'.replace(':table', tableName), window.location.origin);
                            url.searchParams.set('page', page);
                            url.searchParams.set('per_page', this.modalPerPage);
                            if (this.modalSearch) {
                                url.searchParams.set('search', this.modalSearch);
                            }

                            const response = await fetch(url.toString(), {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                }
                            });

                            if (response.ok) {
                                this.tableData = await response.json();
                            }
                        } catch (e) {
                            console.error('Error al consultar tabla:', e);
                        } finally {
                            this.dataLoading = false;
                        }
                    },

                    focusTableOnCanvas(tableName) {
                        const table = this.tables.find(t => t.name === tableName);
                        if (!table) return;

                        const viewport = document.getElementById('diagram-viewport');
                        const vW = viewport ? viewport.clientWidth : 800;
                        const vH = viewport ? viewport.clientHeight : 600;

                        this.panX = Math.round(vW / 2 - (table.position.x + 145) * this.zoom);
                        this.panY = Math.round(vH / 2 - (table.position.y + 100) * this.zoom);
                        this.hoveredTable = tableName;
                    },

                    generateSql(table) {
                        if (!table) return '';
                        let sql = '-- Esquema relacional para la entidad: ' + table.name + '\n';
                        sql += '-- Columnas: ' + table.columns.length + ' | Registros actuales: ' + table.rows_count + '\n\n';
                        sql += 'CREATE TABLE ' + table.name + ' (\n';

                        const colLines = table.columns.map(c => {
                            let line = '    ' + c.name + ' ' + c.type.toUpperCase();
                            if (!c.nullable) line += ' NOT NULL';
                            if (c.default !== null) line += ' DEFAULT ' + c.default;
                            if (c.is_primary) line += ' PRIMARY KEY';
                            return line;
                        });

                        sql += colLines.join(',\n');

                        if (table.foreign_keys && table.foreign_keys.length > 0) {
                            sql += ',\n\n    -- Claves foráneas (Relaciones):\n';
                            const fkLines = table.foreign_keys.map(fk =>
                                '    CONSTRAINT ' + fk.name + ' FOREIGN KEY (' + fk.columns.join(', ') + ') REFERENCES ' + fk.foreign_table + ' (' + fk.foreign_columns.join(', ') + ') ON DELETE ' + fk.on_delete.toUpperCase()
                            );
                            sql += fkLines.join(',\n');
                        }

                        sql += '\n);\n\n';
                        sql += '-- Muestra rápida:\nSELECT * FROM ' + table.name + ' LIMIT 15;\n';
                        return sql;
                    },

                    async copySql() {
                        const sql = this.generateSql(this.currentTable);
                        try {
                            await navigator.clipboard.writeText(sql);
                            this.copiedSql = true;
                            setTimeout(() => this.copiedSql = false, 2500);
                        } catch (e) {
                            alert('No se pudo copiar automáticamente.');
                        }
                    }
                };
            }

            window.erDiagram = createErDiagram;
            if (window.Alpine) {
                window.Alpine.data('erDiagram', createErDiagram);
            } else {
                document.addEventListener('alpine:init', () => {
                    window.Alpine.data('erDiagram', createErDiagram);
                });
            }
        })();
    </script>

    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-2">
                <p class="brutal-kicker text-[var(--color-text-soft)]">Arquitectura Relacional</p>
                <h2 class="text-3xl font-black uppercase leading-none text-[var(--color-ink)]">
                    Diagrama Entidad - Relación (E-R)
                </h2>
                <p class="text-xs text-[var(--color-text-soft)]">
                    Lienzo interactivo de tablas, relaciones foráneas y explorador de contenido en tiempo real para Super Administrador.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <span class="brutal-badge bg-[rgba(37,99,235,0.15)] text-[var(--color-ink)]">
                    {{ $stats['tables_count'] }} tablas
                </span>
                <span class="brutal-badge bg-[rgba(16,185,129,0.15)] text-[var(--color-ink)]">
                    {{ $stats['relations_count'] }} relaciones
                </span>
                <span class="brutal-badge bg-[rgba(245,158,11,0.18)] text-[var(--color-ink)]">
                    Driver: {{ strtoupper($stats['database_driver']) }}
                </span>
                <span class="brutal-badge bg-black text-white dark:bg-white dark:text-black">
                    Super Admin Scope
                </span>
            </div>
        </div>
    </x-slot>

    <div
        x-data="erDiagram({
            tablesData: @js($tables),
            relationshipsData: @js($relationships),
            modulesData: @js($modules),
            statsData: @js($stats),
        })"
        x-init="initDiagram()"
        class="brutal-shell px-3 py-4 sm:px-6 sm:py-6"
    >
        {{-- Barra de herramientas (Filtros, Zoom, Buscador) --}}
        @include('diagramaer::partials.toolbar')

        {{-- Estado vacío: evita un lienzo en blanco si aún no hay tablas --}}
        <div
            x-cloak
            x-show="tables.length === 0"
            class="mb-4 border-[3px] border-dashed border-black bg-[var(--color-surface)] p-6 text-center shadow-[4px_4px_0_0_#111111]"
        >
            <p class="font-display text-lg font-black uppercase text-[var(--color-ink)]">
                No se detectaron tablas en el esquema
            </p>
            <p class="mt-1 text-sm text-[var(--color-text-muted)]">
                Ejecuta las migraciones y pulsa «Refrescar» para reconstruir el diagrama.
            </p>
        </div>

        {{-- Contenedor del Lienzo del Diagrama --}}
        <div
            id="diagram-viewport"
            @mousedown="startPan($event)"
            @mousemove="onMouseMove($event)"
            @mouseup="endDragOrPan()"
            @mouseleave="endDragOrPan()"
            @wheel.prevent="onWheel($event)"
            class="relative h-[72vh] min-h-[620px] w-full cursor-grab overflow-hidden border-[3px] border-black bg-[var(--color-bg)] select-none active:cursor-grabbing shadow-[6px_6px_0_0_#111111]"
            style="background-image: radial-gradient(circle, rgba(0,0,0,0.16) 1.5px, transparent 1.5px); background-size: 26px 26px;"
        >
            {{-- Capa de Transformación (Pan & Zoom) --}}
            <div
                id="diagram-canvas"
                :style="'transform: translate(' + panX + 'px, ' + panY + 'px) scale(' + zoom + '); transform-origin: 0 0;'"
                class="absolute left-0 top-0 h-[2800px] w-[3600px]"
            >
                {{-- Capa SVG de Conectores y Relaciones inyectada dinámicamente --}}
                <svg
                    x-show="showRelations"
                    class="pointer-events-none absolute inset-0 h-full w-full"
                    xmlns="http://www.w3.org/2000/svg"
                    x-html="svgContent"
                ></svg>

                {{-- Nodos de Tablas del Diagrama --}}
                <template x-for="table in tables" :key="table.name">
                    <div
                        :id="'table-card-' + table.name"
                        x-show="isTableVisible(table)"
                        :style="'left: ' + (table.position ? table.position.x : 100) + 'px; top: ' + (table.position ? table.position.y : 100) + 'px;'"
                        :class="[
                            isTableHighlighted(table) ? 'ring-4 ring-[var(--color-primary)] scale-102 z-30 shadow-[10px_10px_0_0_#111111]' : 'z-20 shadow-[6px_6px_0_0_#111111]',
                            isTableDimmed(table.name) ? 'opacity-30' : 'opacity-100',
                            hoveredTable === table.name ? 'scale-101 z-30' : ''
                        ]"
                        @mouseenter="hoveredTable = table.name"
                        @mouseleave="if (hoveredTable === table.name) hoveredTable = null"
                        class="absolute w-[290px] border-[3px] border-black bg-[var(--color-surface)] transition-[box-shadow,opacity] duration-150"
                    >
                        {{-- Cabecera de la Tarjeta (Arrastrable) --}}
                        <div
                            @mousedown.stop="startDragNode($event, table)"
                            class="flex cursor-move items-center justify-between border-b-[2px] border-black p-2.5"
                            :class="table.theme ? (table.theme.bg + ' ' + table.theme.darkBg) : 'bg-neutral-200'"
                        >
                            <div class="flex items-center gap-1.5 overflow-hidden">
                                <span class="h-2.5 w-2.5 border border-black" :style="'background-color: ' + (table.theme ? table.theme.hex : '#111')"></span>
                                <span
                                    class="truncate font-display text-xs font-black uppercase text-black dark:text-white"
                                    x-text="table.name"
                                ></span>
                            </div>

                            <div class="flex items-center gap-1">
                                <span
                                    class="border border-black bg-white px-1.5 py-0.2 text-[10px] font-mono font-bold text-black dark:bg-black dark:text-white"
                                    x-text="'#' + table.rows_count"
                                    title="Cantidad total de registros"
                                ></span>

                                <button
                                    type="button"
                                    @click.stop="openTableModal(table.name, 'diagram')"
                                    class="flex h-5 w-5 items-center justify-center border border-black bg-black text-white hover:bg-neutral-800 dark:bg-white dark:text-black"
                                    title="Abrir diagrama de entidad y registros"
                                >
                                    ↗
                                </button>
                            </div>
                        </div>

                        {{-- Lista de Columnas Relevantes con PK / FK --}}
                        <div class="max-h-[220px] overflow-y-auto divide-y divide-black/10 bg-[var(--color-surface)] p-1 text-[11px] font-mono">
                            <template x-for="(col, colIndex) in (table.columns ? table.columns.slice(0, 7) : [])" :key="col.name">
                                <div
                                    @click="openTableModal(table.name, 'columns')"
                                    class="flex cursor-pointer items-center justify-between px-2 py-1 transition hover:bg-[var(--color-surface-muted)]"
                                    :class="col.is_primary ? 'font-bold' : ''"
                                >
                                    <div class="flex items-center gap-1.5 truncate">
                                        <template x-if="col.is_primary">
                                            <span class="border border-black bg-amber-400 px-1 text-[9px] font-black text-black">PK</span>
                                        </template>
                                        <template x-if="col.is_foreign">
                                            <span class="border border-black bg-blue-400 px-1 text-[9px] font-black text-black">FK</span>
                                        </template>
                                        <span class="truncate text-[var(--color-ink)]" x-text="col.name"></span>
                                    </div>

                                    <span class="shrink-0 text-[10px] text-[var(--color-text-soft)]" x-text="col.type_short"></span>
                                </div>
                            </template>

                            <template x-if="table.columns && table.columns.length > 7">
                                <div
                                    @click="openTableModal(table.name, 'columns')"
                                    class="cursor-pointer px-2 py-1 text-center text-[10px] font-bold text-[var(--color-primary)] hover:underline"
                                >
                                    + <span x-text="table.columns.length - 7"></span> columnas más...
                                </div>
                            </template>
                        </div>

                        {{-- Pie de la Tarjeta con Acciones --}}
                        <div class="flex items-center justify-between border-t-[2px] border-black bg-[var(--color-surface-muted)] p-2">
                            <span class="text-[9px] font-bold uppercase tracking-wider text-[var(--color-text-soft)]" x-text="table.domain"></span>

                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    @click="openTableModal(table.name, 'diagram')"
                                    class="border-[2px] border-black bg-[var(--color-primary)] px-2 py-0.5 text-[10px] font-bold uppercase text-white transition hover:bg-blue-700"
                                    title="Ver diagrama de la entidad y sus relaciones"
                                >
                                    Diagrama
                                </button>
                                <button
                                    type="button"
                                    @click="openTableModal(table.name, 'data')"
                                    class="border-[2px] border-black bg-white px-2 py-0.5 text-[10px] font-bold uppercase text-black transition hover:bg-black hover:text-white dark:bg-black dark:text-white"
                                    title="Ver registros reales de la tabla"
                                >
                                    Datos &raquo;
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Mini Leyenda Flotante en la esquina inferior izquierda --}}
            <div class="absolute bottom-3 left-3 z-30 flex flex-wrap items-center gap-2 border-[2px] border-black bg-[var(--color-surface)]/95 px-3 py-2 text-[10px] font-bold uppercase backdrop-blur-sm shadow-[3px_3px_0_0_#111111]">
                <div class="flex items-center gap-1">
                    <span class="border border-black bg-amber-400 px-1 text-[8px] font-black text-black">PK</span>
                    <span>Clave Primaria</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="border border-black bg-blue-400 px-1 text-[8px] font-black text-black">FK</span>
                    <span>Clave Foránea</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="h-2 w-4 border-b-2 border-black"></span>
                    <span>Relación 1:N</span>
                </div>
                <span class="text-[var(--color-text-soft)]">| Arrastra tarjetas para mover | Rueda = Zoom | Clic = Modal</span>
            </div>
        </div>

        {{-- Modal Interactivo de Detalle, Diagrama y Registros --}}
        @include('diagramaer::partials.table-modal')
    </div>
</x-app-layout>
