<?php

use App\Modules\Organizacion\Http\Controllers\BranchController;
use App\Modules\Organizacion\Http\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('empresas')
    ->name('empresas.')
    ->group(function (): void {
        Route::get('/', [CompanyController::class, 'index'])
            ->middleware('permission:empresas.view')
            ->name('index');

        Route::get('/crear', [CompanyController::class, 'create'])
            ->middleware('permission:empresas.create')
            ->name('create');

        Route::post('/', [CompanyController::class, 'store'])
            ->middleware('permission:empresas.create')
            ->name('store');

        Route::get('/{company}', [CompanyController::class, 'show'])
            ->middleware('permission:empresas.view')
            ->name('show');

        Route::get('/{company}/editar', [CompanyController::class, 'edit'])
            ->middleware('permission:empresas.edit')
            ->name('edit');

        Route::put('/{company}', [CompanyController::class, 'update'])
            ->middleware('permission:empresas.edit')
            ->name('update');

        Route::patch('/{company}/toggle-active', [CompanyController::class, 'toggleActive'])
            ->middleware('permission:empresas.edit')
            ->name('toggleActive');

        Route::delete('/{company}', [CompanyController::class, 'destroy'])
            ->middleware('permission:empresas.delete')
            ->name('destroy');
    });

Route::middleware(['web', 'auth'])
    ->prefix('sucursales')
    ->name('sucursales.')
    ->group(function (): void {
        Route::get('/', [BranchController::class, 'index'])
            ->middleware('permission:sucursales.view')
            ->name('index');

        Route::get('/crear', [BranchController::class, 'create'])
            ->middleware('permission:sucursales.create')
            ->name('create');

        Route::post('/', [BranchController::class, 'store'])
            ->middleware('permission:sucursales.create')
            ->name('store');

        Route::get('/{branch}', [BranchController::class, 'show'])
            ->middleware('permission:sucursales.view')
            ->name('show');

        Route::get('/{branch}/editar', [BranchController::class, 'edit'])
            ->middleware('permission:sucursales.edit')
            ->name('edit');

        Route::put('/{branch}', [BranchController::class, 'update'])
            ->middleware('permission:sucursales.edit')
            ->name('update');

        Route::patch('/{branch}/toggle-active', [BranchController::class, 'toggleActive'])
            ->middleware('permission:sucursales.edit')
            ->name('toggleActive');

        Route::delete('/{branch}', [BranchController::class, 'destroy'])
            ->middleware('permission:sucursales.delete')
            ->name('destroy');
    });
