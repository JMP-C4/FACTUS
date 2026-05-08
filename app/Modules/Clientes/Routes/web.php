<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('clientes')
    ->name('clientes.')
    ->group(function (): void {
        // Rutas del modulo Clientes.
    });
