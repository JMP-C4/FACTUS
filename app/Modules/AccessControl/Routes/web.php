<?php

use App\Modules\AccessControl\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('roles')
    ->name('roles.')
    ->group(function (): void {
        Route::get('/', [RoleController::class, 'index'])
            ->middleware('permission:roles.view')
            ->name('index');

        Route::get('/{role}', [RoleController::class, 'show'])
            ->middleware('permission:roles.view')
            ->name('show');

        Route::get('/{role}/editar', [RoleController::class, 'edit'])
            ->middleware('permission:roles.edit')
            ->name('edit');

        Route::put('/{role}', [RoleController::class, 'update'])
            ->middleware('permission:roles.edit')
            ->name('update');
    });
