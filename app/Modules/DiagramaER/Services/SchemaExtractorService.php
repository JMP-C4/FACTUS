<?php

declare(strict_types=1);

namespace App\Modules\DiagramaER\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Servicio encargado de inspeccionar metadatos del esquema de la base de datos,
 * inferir relaciones entidad-relación, estructurar los dominios/módulos
 * y extraer muestras seguras de datos para el explorador del Super Administrador.
 */
class SchemaExtractorService
{
    private const CACHE_KEY = 'factus_er_diagram_schema_v2';

    private const CACHE_TTL_SECONDS = 180; // 3 minutos

    /**
     * Columnas cuyos valores no deben exponerse en texto plano en la vista.
     */
    private const SENSITIVE_COLUMN_PATTERNS = [
        'password',
        'remember_token',
        'secret',
        'api_token',
        'access_token',
        'private_key',
        'recovery_codes',
        'two_factor_secret',
    ];

    /**
     * Mapeo de tablas conocidas a dominios del sistema Factus OS.
     *
     * @var array<string, string>
     */
    private array $moduleDomains = [
        'companies' => 'Organización',
        'branches' => 'Organización',

        'users' => 'Acceso y RBAC',
        'roles' => 'Acceso y RBAC',
        'permissions' => 'Acceso y RBAC',
        'user_roles' => 'Acceso y RBAC',
        'permission_role' => 'Acceso y RBAC',
        'permission_user' => 'Acceso y RBAC',

        'invoices' => 'Facturación',
        'invoice_items' => 'Facturación',
        'invoice_payments' => 'Facturación',
        'credit_notes' => 'Facturación',
        'numbering_ranges' => 'Facturación',
        'billing_logs' => 'Facturación',

        'products' => 'Catálogo',
        'categories' => 'Catálogo',
        'brands' => 'Catálogo',
        'taxes' => 'Catálogo',

        'warehouses' => 'Inventario',
        'stocks' => 'Inventario',
        'stock_movements' => 'Inventario',

        'customers' => 'Clientes',

        'activity_logs' => 'Auditoría',

        'migrations' => 'Sistema',
        'sessions' => 'Sistema',
        'cache' => 'Sistema',
        'cache_locks' => 'Sistema',
        'jobs' => 'Sistema',
        'failed_jobs' => 'Sistema',
        'job_batches' => 'Sistema',
        'password_reset_tokens' => 'Sistema',
    ];

    /**
     * Paleta de estilos neo-brutalistas por dominio.
     *
     * @var array<string, array{key: string, label: string, accent: string, border: string, bg: string, text: string, darkBg: string, darkText: string, hex: string}>
     */
    private array $domainThemes = [
        'Organización' => [
            'key' => 'organizacion',
            'label' => 'Organización',
            'accent' => 'amber',
            'border' => 'border-amber-500',
            'bg' => 'bg-amber-100',
            'text' => 'text-amber-950',
            'darkBg' => 'dark:bg-amber-950/60',
            'darkText' => 'dark:text-amber-300',
            'hex' => '#f59e0b',
        ],
        'Acceso y RBAC' => [
            'key' => 'rbac',
            'label' => 'Acceso y RBAC',
            'accent' => 'purple',
            'border' => 'border-purple-500',
            'bg' => 'bg-purple-100',
            'text' => 'text-purple-950',
            'darkBg' => 'dark:bg-purple-950/60',
            'darkText' => 'dark:text-purple-300',
            'hex' => '#a855f7',
        ],
        'Facturación' => [
            'key' => 'facturacion',
            'label' => 'Facturación',
            'accent' => 'emerald',
            'border' => 'border-emerald-500',
            'bg' => 'bg-emerald-100',
            'text' => 'text-emerald-950',
            'darkBg' => 'dark:bg-emerald-950/60',
            'darkText' => 'dark:text-emerald-300',
            'hex' => '#10b981',
        ],
        'Catálogo' => [
            'key' => 'catalogo',
            'label' => 'Catálogo',
            'accent' => 'blue',
            'border' => 'border-blue-500',
            'bg' => 'bg-blue-100',
            'text' => 'text-blue-950',
            'darkBg' => 'dark:bg-blue-950/60',
            'darkText' => 'dark:text-blue-300',
            'hex' => '#3b82f6',
        ],
        'Inventario' => [
            'key' => 'inventario',
            'label' => 'Inventario',
            'accent' => 'orange',
            'border' => 'border-orange-500',
            'bg' => 'bg-orange-100',
            'text' => 'text-orange-950',
            'darkBg' => 'dark:bg-orange-950/60',
            'darkText' => 'dark:text-orange-300',
            'hex' => '#f97316',
        ],
        'Clientes' => [
            'key' => 'clientes',
            'label' => 'Clientes',
            'accent' => 'cyan',
            'border' => 'border-cyan-500',
            'bg' => 'bg-cyan-100',
            'text' => 'text-cyan-950',
            'darkBg' => 'dark:bg-cyan-950/60',
            'darkText' => 'dark:text-cyan-300',
            'hex' => '#06b6d4',
        ],
        'Auditoría' => [
            'key' => 'auditoria',
            'label' => 'Auditoría',
            'accent' => 'rose',
            'border' => 'border-rose-500',
            'bg' => 'bg-rose-100',
            'text' => 'text-rose-950',
            'darkBg' => 'dark:bg-rose-950/60',
            'darkText' => 'dark:text-rose-300',
            'hex' => '#f43f5e',
        ],
        'Sistema' => [
            'key' => 'sistema',
            'label' => 'Sistema',
            'accent' => 'slate',
            'border' => 'border-slate-500',
            'bg' => 'bg-slate-200',
            'text' => 'text-slate-900',
            'darkBg' => 'dark:bg-slate-800',
            'darkText' => 'dark:text-slate-300',
            'hex' => '#64748b',
        ],
        'General' => [
            'key' => 'general',
            'label' => 'General',
            'accent' => 'zinc',
            'border' => 'border-zinc-500',
            'bg' => 'bg-zinc-100',
            'text' => 'text-zinc-900',
            'darkBg' => 'dark:bg-zinc-800',
            'darkText' => 'dark:text-zinc-300',
            'hex' => '#71717a',
        ],
    ];

    /**
     * Posiciones visuales por defecto en el lienzo virtual (X, Y).
     *
     * @var array<string, array{x: int, y: int}>
     */
    private array $defaultPositions = [
        // Organización & RBAC (Noroeste)
        'companies' => ['x' => 450,  'y' => 100],
        'branches' => ['x' => 880,  'y' => 100],
        'users' => ['x' => 880,  'y' => 460],
        'roles' => ['x' => 450,  'y' => 460],
        'permissions' => ['x' => 80,   'y' => 460],
        'user_roles' => ['x' => 670,  'y' => 760],
        'permission_role' => ['x' => 260,  'y' => 760],
        'permission_user' => ['x' => 80,   'y' => 760],

        // Clientes y Facturación (Centro)
        'customers' => ['x' => 1340, 'y' => 100],
        'numbering_ranges' => ['x' => 1340, 'y' => 380],
        'invoices' => ['x' => 1340, 'y' => 680],
        'invoice_items' => ['x' => 1780, 'y' => 680],
        'invoice_payments' => ['x' => 1340, 'y' => 1100],
        'credit_notes' => ['x' => 920,  'y' => 1020],
        'billing_logs' => ['x' => 450,  'y' => 1020],

        // Catálogo e Inventario (Noreste / Este)
        'brands' => ['x' => 1780, 'y' => 100],
        'categories' => ['x' => 2180, 'y' => 100],
        'taxes' => ['x' => 2580, 'y' => 100],
        'products' => ['x' => 2180, 'y' => 420],
        'warehouses' => ['x' => 2580, 'y' => 720],
        'stocks' => ['x' => 2180, 'y' => 880],
        'stock_movements' => ['x' => 1780, 'y' => 1100],

        // Auditoría y Sistema (Sur)
        'activity_logs' => ['x' => 450,  'y' => 1320],
        'sessions' => ['x' => 80,   'y' => 1320],
        'migrations' => ['x' => 80,   'y' => 1560],
        'cache' => ['x' => 450,  'y' => 1560],
        'cache_locks' => ['x' => 800,  'y' => 1560],
        'jobs' => ['x' => 1150, 'y' => 1560],
        'failed_jobs' => ['x' => 1500, 'y' => 1560],
        'job_batches' => ['x' => 1850, 'y' => 1560],
        'password_reset_tokens' => ['x' => 2200, 'y' => 1560],
    ];

    /**
     * Obtiene el esquema completo de la base de datos procesado para el diagrama.
     *
     * @return array<string, mixed>
     */
    public function getSchema(bool $forceRefresh = false): array
    {
        if ($forceRefresh) {
            Cache::forget(self::CACHE_KEY);
        }

        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function (): array {
            return $this->buildSchema();
        });
    }

    /**
     * Extrae y procesa todas las tablas, columnas, índices y relaciones.
     *
     * @return array<string, mixed>
     */
    private function buildSchema(): array
    {
        $rawTables = Schema::getTables();
        $tables = [];
        $relationships = [];
        $domainCounts = [];
        $totalColumns = 0;

        // Fase 1: Extracción básica de cada tabla
        foreach ($rawTables as $rawTable) {
            $tableName = is_object($rawTable) ? $rawTable->name : $rawTable['name'];

            // Omitir vistas internas de postgres si correspondiera
            if (str_starts_with($tableName, 'pg_') || str_starts_with($tableName, 'sql_')) {
                continue;
            }

            $domain = $this->resolveDomain($tableName);
            $theme = $this->domainThemes[$domain] ?? $this->domainThemes['General'];

            $domainCounts[$domain] = ($domainCounts[$domain] ?? 0) + 1;

            $columnsData = $this->extractColumns($tableName);
            $indexesData = $this->extractIndexes($tableName);
            $foreignKeys = $this->extractForeignKeys($tableName);

            $primaryKeys = array_values(array_filter(
                $columnsData,
                static fn (array $c): bool => $c['is_primary']
            ));
            $primaryKeyNames = array_column($primaryKeys, 'name');

            // Conteo rápido de registros
            $rowCount = $this->safeRowCount($tableName);

            // Tamaño aproximado
            $sizeFormatted = $this->formatSize(is_object($rawTable) ? ($rawTable->size ?? null) : ($rawTable['size'] ?? null));

            $pos = $this->defaultPositions[$tableName] ?? [
                'x' => 100 + (count($tables) % 6) * 360,
                'y' => 100 + intdiv(count($tables), 6) * 340,
            ];

            $tables[$tableName] = [
                'name' => $tableName,
                'domain' => $domain,
                'theme' => $theme,
                'comment' => is_object($rawTable) ? ($rawTable->comment ?? null) : ($rawTable['comment'] ?? null),
                'rows_count' => $rowCount,
                'size' => $sizeFormatted,
                'columns_count' => count($columnsData),
                'columns' => $columnsData,
                'primary_keys' => $primaryKeyNames,
                'foreign_keys' => $foreignKeys,
                'indexes' => $indexesData,
                'outgoing_relations' => [],
                'incoming_relations' => [],
                'position' => $pos,
            ];

            $totalColumns += count($columnsData);
        }

        // Fase 2: Mapeo de relaciones salientes y entrantes (E-R)
        foreach ($tables as $sourceName => &$sourceData) {
            foreach ($sourceData['foreign_keys'] as $fk) {
                $targetName = $fk['foreign_table'];
                $sourceCols = $fk['columns'];
                $targetCols = $fk['foreign_columns'];
                $fkName = $fk['name'];

                $relId = "{$sourceName}_".implode('_', $sourceCols)."_{$targetName}_".implode('_', $targetCols);

                $relItem = [
                    'id' => $relId,
                    'name' => $fkName,
                    'source_table' => $sourceName,
                    'source_columns' => $sourceCols,
                    'target_table' => $targetName,
                    'target_columns' => $targetCols,
                    'on_update' => $fk['on_update'],
                    'on_delete' => $fk['on_delete'],
                    'source_domain' => $sourceData['domain'],
                    'color' => $sourceData['theme']['hex'],
                ];

                $relationships[] = $relItem;
                $sourceData['outgoing_relations'][] = $relItem;

                if (isset($tables[$targetName])) {
                    $tables[$targetName]['incoming_relations'][] = $relItem;
                }
            }
        }
        unset($sourceData);

        // Fase 3: Lista estructurada de módulos/dominios para filtros en toolbar
        $modulesList = [];
        foreach ($this->domainThemes as $domainName => $theme) {
            $count = $domainCounts[$domainName] ?? 0;
            if ($count > 0) {
                $modulesList[] = [
                    'name' => $domainName,
                    'key' => $theme['key'],
                    'count' => $count,
                    'theme' => $theme,
                ];
            }
        }

        return [
            'tables' => array_values($tables),
            'tables_by_name' => $tables,
            'relationships' => $relationships,
            'modules' => $modulesList,
            'stats' => [
                'tables_count' => count($tables),
                'relations_count' => count($relationships),
                'total_columns' => $totalColumns,
                'database_driver' => DB::connection()->getDriverName(),
                'database_name' => DB::connection()->getDatabaseName(),
                'cached_at' => Carbon::now()->format('H:i:s'),
            ],
        ];
    }

    /**
     * Extrae información estructurada de las columnas de una tabla.
     *
     * @return array<int, array<string, mixed>>
     */
    private function extractColumns(string $tableName): array
    {
        $rawColumns = Schema::getColumns($tableName);
        $rawIndexes = Schema::getIndexes($tableName);
        $rawFks = Schema::getForeignKeys($tableName);

        // Detectar claves primarias desde los índices
        $pkColumns = [];
        foreach ($rawIndexes as $idx) {
            if (! empty($idx['primary'])) {
                $pkColumns = array_merge($pkColumns, $idx['columns'] ?? []);
            }
        }

        // Mapear claves foráneas indexadas por nombre de columna
        $fkMap = [];
        foreach ($rawFks as $fk) {
            foreach ($fk['columns'] as $idx => $col) {
                $fkMap[$col] = [
                    'foreign_table' => $fk['foreign_table'],
                    'foreign_column' => $fk['foreign_columns'][$idx] ?? 'id',
                    'constraint' => $fk['name'],
                ];
            }
        }

        $columns = [];
        foreach ($rawColumns as $c) {
            $colName = $c['name'];
            $isPk = in_array($colName, $pkColumns, true);
            $isFk = isset($fkMap[$colName]);
            $fkTarget = $fkMap[$colName] ?? null;

            $fullType = $c['type'] ?? $c['type_name'] ?? 'string';
            $shortType = $this->cleanDisplayType($fullType);

            $columns[] = [
                'name' => $colName,
                'type' => $fullType,
                'type_short' => $shortType,
                'type_name' => $c['type_name'] ?? '',
                'nullable' => (bool) ($c['nullable'] ?? false),
                'default' => $c['default'] ?? null,
                'auto_increment' => (bool) ($c['auto_increment'] ?? false),
                'comment' => $c['comment'] ?? null,
                'is_primary' => $isPk,
                'is_foreign' => $isFk,
                'foreign_target' => $fkTarget,
                'is_sensitive' => $this->isSensitiveColumn($colName),
            ];
        }

        return $columns;
    }

    /**
     * Extrae los índices de una tabla.
     *
     * @return array<int, array<string, mixed>>
     */
    private function extractIndexes(string $tableName): array
    {
        $raw = Schema::getIndexes($tableName);
        $indexes = [];

        foreach ($raw as $idx) {
            $indexes[] = [
                'name' => $idx['name'],
                'columns' => $idx['columns'] ?? [],
                'type' => $idx['type'] ?? 'btree',
                'unique' => (bool) ($idx['unique'] ?? false),
                'primary' => (bool) ($idx['primary'] ?? false),
            ];
        }

        return $indexes;
    }

    /**
     * Extrae claves foráneas con tipos consistentes.
     *
     * @return array<int, array<string, mixed>>
     */
    private function extractForeignKeys(string $tableName): array
    {
        $raw = Schema::getForeignKeys($tableName);
        $fks = [];

        foreach ($raw as $fk) {
            $fks[] = [
                'name' => $fk['name'],
                'columns' => $fk['columns'] ?? [],
                'foreign_table' => $fk['foreign_table'],
                'foreign_columns' => $fk['foreign_columns'] ?? [],
                'on_update' => strtolower($fk['on_update'] ?? 'no action'),
                'on_delete' => strtolower($fk['on_delete'] ?? 'no action'),
            ];
        }

        return $fks;
    }

    /**
     * Obtiene datos de registros para una tabla con paginación, búsqueda segura y enmascaramiento.
     *
     * @return array<string, mixed>
     */
    public function getTableData(
        string $tableName,
        int $page = 1,
        int $perPage = 15,
        ?string $search = null
    ): array {
        // Validación estricta de nombre de tabla para prevenir inyecciones
        $allowedTables = array_column(Schema::getTables(), 'name');
        if (! in_array($tableName, $allowedTables, true)) {
            abort(404, "La tabla '{$tableName}' no existe en la base de datos.");
        }

        $perPage = max(5, min(50, $perPage));
        $page = max(1, $page);

        $columns = Schema::getColumns($tableName);
        $columnNames = array_column($columns, 'name');

        $query = DB::table($tableName);

        // Búsqueda inteligente si se proporcionó un término
        if ($search !== null && trim($search) !== '') {
            $searchTerm = '%'.trim($search).'%';
            $driver = DB::connection()->getDriverName();
            $likeOperator = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';

            $query->where(function ($q) use ($columns, $searchTerm, $likeOperator) {
                foreach ($columns as $col) {
                    $typeName = strtolower($col['type_name'] ?? '');
                    // Buscar en columnas de texto/alfanuméricas
                    if (in_array($typeName, ['varchar', 'text', 'char', 'string', 'bpchar'], true)) {
                        $q->orWhere($col['name'], $likeOperator, $searchTerm);
                    }
                }
            });
        }

        $total = $query->count();

        // Determinar orden natural: clave primaria o 'id' o primera columna
        $indexes = Schema::getIndexes($tableName);
        $primaryCol = null;
        foreach ($indexes as $idx) {
            if (! empty($idx['primary']) && ! empty($idx['columns'][0])) {
                $primaryCol = $idx['columns'][0];
                break;
            }
        }
        $primaryCol ??= in_array('id', $columnNames, true) ? 'id' : ($columnNames[0] ?? null);

        if ($primaryCol !== null) {
            $query->orderBy($primaryCol, 'desc');
        }

        $records = $query->forPage($page, $perPage)->get();

        // Transformar registros sanitizando campos confidenciales
        $sanitizedRecords = $records->map(function ($row) {
            $rowArray = (array) $row;
            foreach ($rowArray as $col => $val) {
                if ($this->isSensitiveColumn($col) && $val !== null) {
                    $rowArray[$col] = '••••••••••••';
                } elseif (is_string($val) && strlen($val) > 250) {
                    $rowArray[$col] = Str::limit($val, 250);
                }
            }

            return $rowArray;
        })->toArray();

        $lastPage = max(1, (int) ceil($total / $perPage));

        return [
            'success' => true,
            'table' => $tableName,
            'domain' => $this->resolveDomain($tableName),
            'columns' => $this->extractColumns($tableName),
            'column_names' => $columnNames,
            'data' => $sanitizedRecords,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $lastPage,
            'has_more' => $page < $lastPage,
            'search' => $search,
        ];
    }

    /**
     * Obtiene detalles extendidos de una sola tabla (esquema, columnas, relaciones, estadísticas).
     *
     * @return array<string, mixed>
     */
    public function getTableDetails(string $tableName): array
    {
        $schema = $this->getSchema();
        $tableData = $schema['tables_by_name'][$tableName] ?? null;

        if ($tableData === null) {
            abort(404, "Tabla '{$tableName}' no encontrada.");
        }

        return [
            'success' => true,
            'table' => $tableData,
            'stats' => $schema['stats'],
        ];
    }

    /**
     * Asocia una tabla a su dominio correspondiente.
     */
    private function resolveDomain(string $tableName): string
    {
        if (isset($this->moduleDomains[$tableName])) {
            return $this->moduleDomains[$tableName];
        }

        // Reglas heurísticas por prefijo
        if (str_starts_with($tableName, 'inv_') || str_contains($tableName, 'stock') || str_contains($tableName, 'warehouse')) {
            return 'Inventario';
        }
        if (str_starts_with($tableName, 'fac_') || str_contains($tableName, 'invoice') || str_contains($tableName, 'billing')) {
            return 'Facturación';
        }
        if (str_starts_with($tableName, 'prod_') || str_contains($tableName, 'category') || str_contains($tableName, 'brand')) {
            return 'Catálogo';
        }
        if (str_contains($tableName, 'user') || str_contains($tableName, 'role') || str_contains($tableName, 'permission')) {
            return 'Acceso y RBAC';
        }
        if (str_contains($tableName, 'log') || str_contains($tableName, 'audit')) {
            return 'Auditoría';
        }

        return 'General';
    }

    /**
     * Limpia y compacta el tipo SQL para una visualización más estética.
     */
    private function cleanDisplayType(string $fullType): string
    {
        $type = strtoupper($fullType);
        $type = str_replace(' WITHOUT TIME ZONE', '', $type);
        $type = str_replace(' WITH TIME ZONE', ' TZ', $type);
        $type = str_replace('CHARACTER VARYING', 'VARCHAR', $type);

        return $type;
    }

    /**
     * Verifica si una columna es sensible.
     */
    private function isSensitiveColumn(string $columnName): bool
    {
        $lower = strtolower($columnName);
        foreach (self::SENSITIVE_COLUMN_PATTERNS as $pattern) {
            if (str_contains($lower, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Conteo seguro de registros sin bloquear la base de datos.
     */
    private function safeRowCount(string $tableName): int
    {
        try {
            return DB::table($tableName)->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Formatea el tamaño en bytes a KB/MB legibles.
     */
    private function formatSize(?int $bytes): string
    {
        if ($bytes === null || $bytes <= 0) {
            return 'N/A';
        }

        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1).' KB';
        }

        return round($bytes / 1048576, 2).' MB';
    }
}
