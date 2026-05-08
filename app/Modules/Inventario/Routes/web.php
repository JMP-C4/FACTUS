<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('inventario')
    ->name('inventario.')
    ->group(function (): void {
        // Rutas del modulo Inventario.
    });
