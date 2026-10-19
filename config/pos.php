<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Código de autorización para retirar productos del carrito
    |--------------------------------------------------------------------------
    |
    | Código maestro que un cajero puede ingresar en el POS para autorizar el
    | retiro de artículos del carrito. Si queda vacío, la autorización por
    | código queda deshabilitada y sólo funcionará el método alternativo con
    | credenciales de supervisor.
    |
     */
    'authorization_code' => env('POS_AUTHORIZATION_CODE'),
];
