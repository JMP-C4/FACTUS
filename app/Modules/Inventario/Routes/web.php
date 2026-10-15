<?php

use App\Modules\Inventario\Http\Controllers\StockMovementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('inventario')
    ->name('inventario.')
    ->group(function (): void {
        // Listado de movimientos.
        Route::get('/movimientos', [StockMovementController::class, 'index'])
            ->middleware('permission:inventario.view')
            ->name('movimientos.index');

        // Entradas de stock.
        Route::get('/entradas/crear', [StockMovementController::class, 'createEntry'])
            ->middleware('permission:inventario.entrada')
            ->name('entradas.create');

        Route::post('/entradas', [StockMovementController::class, 'storeEntry'])
            ->middleware('permission:inventario.entrada')
            ->name('entradas.store');

        // Ajustes de stock.
        Route::get('/ajustes/crear', [StockMovementController::class, 'createAdjustment'])
            ->middleware('permission:inventario.ajuste')
            ->name('ajustes.create');

        Route::post('/ajustes', [StockMovementController::class, 'storeAdjustment'])
            ->middleware('permission:inventario.ajuste')
            ->name('ajustes.store');
    });
