<?php

use App\Modules\Usuarios\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('usuarios')
    ->name('usuarios.')
    ->group(function (): void {
        Route::get('/', [UserController::class, 'index'])
            ->middleware('permission:users.view')
            ->name('index');

        Route::get('/crear', [UserController::class, 'create'])
            ->middleware('permission:users.create')
            ->name('create');

        Route::post('/', [UserController::class, 'store'])
            ->middleware('permission:users.create')
            ->name('store');

        Route::get('/{user}', [UserController::class, 'show'])
            ->middleware('permission:users.view')
            ->name('show');

        Route::get('/{user}/editar', [UserController::class, 'edit'])
            ->middleware('permission:users.edit')
            ->name('edit');

        Route::put('/{user}', [UserController::class, 'update'])
            ->middleware('permission:users.edit')
            ->name('update');

        Route::delete('/{user}', [UserController::class, 'destroy'])
            ->middleware('permission:users.delete')
            ->name('destroy');
    });
