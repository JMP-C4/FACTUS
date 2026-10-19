<?php

use App\Modules\Automatizacion\Http\Controllers\AutomationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('automatizacion')
    ->name('automatizacion.')
    ->group(function (): void {
        Route::get('/', [AutomationController::class, 'index'])
            ->middleware('permission:automatizacion.view')
            ->name('index');

        Route::post('/{process}/ejecutar', [AutomationController::class, 'run'])
            ->middleware('permission:automatizacion.view')
            ->name('run');
    });
