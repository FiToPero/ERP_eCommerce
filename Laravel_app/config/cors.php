<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        // Esta variable permite sobrescribir el frontend principal sin tocar el código por entorno.
        env('FRONTEND_URL', 'http://cap-erp-ecommerce-dev.com'),
        // Este dominio permite a la SPA de Vue en desarrollo consumir la API de Laravel sin usar puertos explícitos.
        'http://cap-erp-ecommerce-dev.com',
        // Este dominio permite al frontend productivo consumir la API o Sanctum desde su hostname final.
        'https://cap-erp-ecommerce.com',
        // Este origen local se conserva para pruebas rápidas fuera del proxy Nginx.
        'http://localhost:8081',
        // Este origen local cubre el dev server directo de Vue si alguna vez lo pruebas sin Nginx.
        'http://localhost:5174',
        // Este origen local cubre casos donde Laravel Vite necesite requests cruzadas en desarrollo manual.
        'http://localhost:5173',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
