<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Issuer Data
    |--------------------------------------------------------------------------
    |
    | Datos usados por el emisor local para numeración y sellos simulados.
    |
    */
    'prefix' => env('BILLING_PREFIX', 'FAC'),
    'issuer_name' => env('BILLING_ISSUER_NAME', 'FACTUS'),
    'environment' => env('BILLING_ENVIRONMENT', 'local'),
];
