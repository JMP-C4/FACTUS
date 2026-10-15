<?php

use App\Http\Middleware\ScopeCompanyBranch;
use App\Modules\AccessControl\Http\Middleware\CheckPermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /**
         * Registra el middleware de verificación de permisos.
         * Se puede usar en rutas como: ->middleware('permission:productos.crear')
         */
        $middleware->alias([
            'permission' => CheckPermission::class,
            'scope.company_branch' => ScopeCompanyBranch::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
