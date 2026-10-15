<?php

use App\Modules\Clientes\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('clientes')
    ->name('clientes.')
    ->group(function (): void {
        // Listado y búsqueda.
        Route::get('/', [CustomerController::class, 'index'])
            ->middleware('permission:clientes.view')
            ->name('index');

        Route::get('/buscar', [CustomerController::class, 'search'])
            ->middleware('permission:clientes.view')
            ->name('search');

        // Crear cliente.
        Route::get('/crear', [CustomerController::class, 'create'])
            ->middleware('permission:clientes.create')
            ->name('create');

        Route::post('/', [CustomerController::class, 'store'])
            ->middleware('permission:clientes.create')
            ->name('store');

        // Ver cliente.
        Route::get('/{customer}', [CustomerController::class, 'show'])
            ->middleware('permission:clientes.view')
            ->name('show');

        // Editar cliente.
        Route::get('/{customer}/editar', [CustomerController::class, 'edit'])
            ->middleware('permission:clientes.edit')
            ->name('edit');

        Route::put('/{customer}', [CustomerController::class, 'update'])
            ->middleware('permission:clientes.edit')
            ->name('update');

        // Eliminar cliente.
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])
            ->middleware('permission:clientes.delete')
            ->name('destroy');
    });
