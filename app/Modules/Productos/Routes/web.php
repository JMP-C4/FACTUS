<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('productos')
    ->name('productos.')
    ->group(function (): void {
        // Rutas del modulo Productos.
    });
