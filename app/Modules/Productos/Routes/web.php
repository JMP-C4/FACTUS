<?php

use App\Modules\Productos\Http\Controllers\BrandController;
use App\Modules\Productos\Http\Controllers\CategoryController;
use App\Modules\Productos\Http\Controllers\ProductController;
use App\Modules\Productos\Http\Controllers\TaxController;
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

        // Catálogo de impuestos (debe ir definido antes de /{product}).
        Route::get('/impuestos', [TaxController::class, 'index'])
            ->middleware('permission:impuestos.manage')
            ->name('impuestos.index');

        Route::get('/impuestos/crear', [TaxController::class, 'create'])
            ->middleware('permission:impuestos.manage')
            ->name('impuestos.create');

        Route::post('/impuestos', [TaxController::class, 'store'])
            ->middleware('permission:impuestos.manage')
            ->name('impuestos.store');

        Route::get('/impuestos/{tax}/editar', [TaxController::class, 'edit'])
            ->middleware('permission:impuestos.manage')
            ->name('impuestos.edit');

        Route::put('/impuestos/{tax}', [TaxController::class, 'update'])
            ->middleware('permission:impuestos.manage')
            ->name('impuestos.update');

        Route::patch('/impuestos/{tax}/estado', [TaxController::class, 'toggleActive'])
            ->middleware('permission:impuestos.manage')
            ->name('impuestos.toggleActive');

        Route::delete('/impuestos/{tax}', [TaxController::class, 'destroy'])
            ->middleware('permission:impuestos.manage')
            ->name('impuestos.destroy');

        // Catálogo de categorías.
        Route::middleware('permission:categorias.manage')
            ->prefix('categorias')
            ->name('categorias.')
            ->group(function (): void {
                Route::get('/', [CategoryController::class, 'index'])->name('index');
                Route::get('/crear', [CategoryController::class, 'create'])->name('create');
                Route::post('/', [CategoryController::class, 'store'])->name('store');
                Route::get('/{category}/editar', [CategoryController::class, 'edit'])->name('edit');
                Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
                Route::patch('/{category}/toggle-active', [CategoryController::class, 'toggleActive'])->name('toggleActive');
                Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
            });

        // Catálogo de marcas.
        Route::middleware('permission:marcas.manage')
            ->prefix('marcas')
            ->name('marcas.')
            ->group(function (): void {
                Route::get('/', [BrandController::class, 'index'])->name('index');
                Route::get('/crear', [BrandController::class, 'create'])->name('create');
                Route::post('/', [BrandController::class, 'store'])->name('store');
                Route::get('/{brand}/editar', [BrandController::class, 'edit'])->name('edit');
                Route::put('/{brand}', [BrandController::class, 'update'])->name('update');
                Route::patch('/{brand}/toggle-active', [BrandController::class, 'toggleActive'])->name('toggleActive');
                Route::delete('/{brand}', [BrandController::class, 'destroy'])->name('destroy');
            });

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
