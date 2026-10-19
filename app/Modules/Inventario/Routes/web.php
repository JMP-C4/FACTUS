<?php

use App\Modules\Inventario\Http\Controllers\StockMovementController;
use App\Modules\Inventario\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('inventario')
    ->name('inventario.')
    ->group(function (): void {
        // Catálogo de almacenes.
        Route::middleware('permission:almacenes.manage')
            ->prefix('almacenes')
            ->name('almacenes.')
            ->group(function (): void {
                Route::get('/', [WarehouseController::class, 'index'])->name('index');
                Route::get('/crear', [WarehouseController::class, 'create'])->name('create');
                Route::post('/', [WarehouseController::class, 'store'])->name('store');
                Route::get('/{warehouse}/editar', [WarehouseController::class, 'edit'])->name('edit');
                Route::put('/{warehouse}', [WarehouseController::class, 'update'])->name('update');
                Route::patch('/{warehouse}/predeterminado', [WarehouseController::class, 'setDefault'])->name('setDefault');
                Route::delete('/{warehouse}', [WarehouseController::class, 'destroy'])->name('destroy');
            });

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
