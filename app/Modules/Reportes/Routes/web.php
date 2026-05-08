<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('reportes')
    ->name('reportes.')
    ->group(function (): void {
        // Rutas del modulo Reportes.
    });
