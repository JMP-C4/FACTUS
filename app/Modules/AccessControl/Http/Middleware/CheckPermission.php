<?php

namespace App\Modules\AccessControl\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckPermission Middleware
 *
 * Verifica si el usuario autenticado tiene los permisos requeridos para acceder
 * a una ruta protegida.
 *
 * Uso en rutas:
 *   Route::post('/productos', [ProductoController::class, 'store'])
 *       ->middleware('permission:productos.crear');
 *
 *   // Múltiples permisos (se requieren todos):
 *   ->middleware('permission:usuarios.crear,usuarios.editar');
 *
 *   // Usando el método en RouteServiceProvider:
 *   Route::group(['middleware' => 'permission:productos.ver'], function () {
 *       // Rutas protegidas
 *   });
 */
class CheckPermission
{
    /**
     * Procesa la solicitud y verifica los permisos requeridos.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  ...$permissions  Nombres de permisos requeridos (separados por comas)
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        // Si no hay usuario autenticado, denegar acceso
        if (! $request->user()) {
            return response()->json(
                ['message' => 'No autenticado'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $user = $request->user();

        // Si el usuario es super_admin, permitir acceso inmediato
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Verificar permisos requeridos
        // Si se proporciona más de un permiso separado por coma, se requieren todos
        foreach ($permissions as $permission) {
            // Permitir permisos separados por coma en un mismo argumento
            $requiredPermissions = array_map('trim', explode(',', $permission));

            // Verificar si el usuario tiene todos los permisos requeridos
            if (! $user->hasAllPermissions($requiredPermissions)) {
                // Log de intento de acceso no autorizado para auditoría
                \Log::warning(
                    'Acceso denegado por permisos insuficientes',
                    [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'required_permissions' => $requiredPermissions,
                        'route' => $request->getPathInfo(),
                        'method' => $request->getMethod(),
                        'ip' => $request->ip(),
                    ]
                );

                // Retornar respuesta de no autorizado
                // Si es AJAX, retornar JSON; de lo contrario, redirigir
                if ($request->expectsJson()) {
                    return response()->json(
                        [
                            'message' => 'No tienes permisos para acceder a este recurso',
                            'required_permissions' => $requiredPermissions,
                        ],
                        Response::HTTP_FORBIDDEN
                    );
                }

                return response()->view('errors.403', [
                    'message' => 'No tienes permisos suficientes',
                ], Response::HTTP_FORBIDDEN);
            }
        }

        return $next($request);
    }
}
