<?php

use App\Modules\Productos\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('productos')
    ->name('productos.')
    ->group(function (): void {
        // Listado y búsqueda (requiere ver productos).
        Route::get('/', [ProductController::class, 'index'])
            ->middleware('permission:productos.view')
            ->name('index');

        Route::get('/buscar-codigo', [ProductController::class, 'findByBarcode'])
            ->middleware('permission:productos.view')
            ->name('findByBarcode');

        // Crear producto.
        Route::get('/crear', [ProductController::class, 'create'])
            ->middleware('permission:productos.create')
            ->name('create');

        Route::post('/', [ProductController::class, 'store'])
            ->middleware('permission:productos.create')
            ->name('store');

        // Ver producto.
        Route::get('/{product}', [ProductController::class, 'show'])
            ->middleware('permission:productos.view')
            ->name('show');

        // Editar producto.
        Route::get('/{product}/editar', [ProductController::class, 'edit'])
            ->middleware('permission:productos.edit')
            ->name('edit');

        Route::put('/{product}', [ProductController::class, 'update'])
            ->middleware('permission:productos.edit')
            ->name('update');

        // Activar/desactivar producto.
        Route::patch('/{product}/toggle-active', [ProductController::class, 'toggleActive'])
            ->middleware('permission:productos.edit')
            ->name('toggleActive');

        // Eliminar producto.
        Route::delete('/{product}', [ProductController::class, 'destroy'])
            ->middleware('permission:productos.delete')
            ->name('destroy');
    });
