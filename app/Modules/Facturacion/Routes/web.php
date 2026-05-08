<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('facturacion')
    ->name('facturacion.')
    ->group(function (): void {
        // Rutas del modulo Facturacion.
    });
