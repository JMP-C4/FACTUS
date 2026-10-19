<?php

declare(strict_types=1);

namespace App\Modules\DiagramaER\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\DiagramaER\Services\SchemaExtractorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controlador de la visualización del Diagrama Entidad-Relación y explorador
 * de datos exclusivo para usuarios con el rol Super Administrador.
 */
class DiagramaERController extends Controller
{
    public function __construct(
        private readonly SchemaExtractorService $schemaService
    ) {}

    /**
     * Vista principal del lienzo interactivo del Diagrama E-R.
     */
    public function index(Request $request): View
    {
        $this->authorizeSuperAdmin($request);

        $forceRefresh = $request->boolean('refresh');
        $schema = $this->schemaService->getSchema($forceRefresh);

        return view('diagramaer::index', [
            'schema' => $schema,
            'tables' => $schema['tables'],
            'relationships' => $schema['relationships'],
            'modules' => $schema['modules'],
            'stats' => $schema['stats'],
        ]);
    }

    /**
     * Endpoint API para consultar el esquema relacional en formato JSON.
     */
    public function schema(Request $request): JsonResponse
    {
        $this->authorizeSuperAdmin($request);

        $forceRefresh = $request->boolean('refresh');
        $schema = $this->schemaService->getSchema($forceRefresh);

        return response()->json([
            'success' => true,
            'schema' => $schema,
        ]);
    }

    /**
     * Endpoint API para consultar el contenido paginado y seguro de una tabla específica
     * al desplegar el modal.
     */
    public function tableData(Request $request, string $table): JsonResponse
    {
        $this->authorizeSuperAdmin($request);

        $page = $request->integer('page', 1);
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');

        $result = $this->schemaService->getTableData(
            tableName: $table,
            page: $page,
            perPage: $perPage,
            search: is_string($search) ? $search : null
        );

        return response()->json($result);
    }

    /**
     * Endpoint API para consultar detalles del esquema y relaciones de una tabla específica.
     */
    public function tableDetails(Request $request, string $table): JsonResponse
    {
        $this->authorizeSuperAdmin($request);

        $result = $this->schemaService->getTableDetails($table);

        return response()->json($result);
    }

    /**
     * Asegura estrictamente que sólo el rol Super Administrador pueda acceder a la herramienta.
     */
    private function authorizeSuperAdmin(Request $request): void
    {
        abort_unless(
            $request->user()?->isSuperAdmin() === true,
            403,
            'Acceso restringido: Esta visualización de arquitectura de base de datos está reservada exclusivamente para el Super Administrador.'
        );
    }
}
