<?php

use App\Modules\Reportes\Http\Controllers\ReportesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('reportes')
    ->name('reportes.')
    ->group(function (): void {
        // Reporte de ventas.
        Route::get('/ventas', [ReportesController::class, 'ventas'])
            ->middleware('permission:reportes.ventas')
            ->name('ventas.index');

        // Reporte de inventario.
        Route::get('/inventario', [ReportesController::class, 'inventario'])
            ->middleware('permission:reportes.inventario')
            ->name('inventario.index');
    });
