<?php

use App\Modules\Auditoria\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('auditoria')
    ->name('auditoria.')
    ->group(function (): void {
        Route::get('/', [ActivityLogController::class, 'index'])
            ->middleware('permission:platform.logs.view')
            ->name('index');

        Route::get('/{log}', [ActivityLogController::class, 'show'])
            ->middleware('permission:platform.logs.view')
            ->name('show');
    });
