<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS Configuration
    |--------------------------------------------------------------------------
    | Este archivo controla los encabezados CORS de tu API.
    | Ajusta los orígenes para desarrollo y producción.
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'], // puedes limitar a GET, POST, PUT, DELETE si quieres

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 3600,

    'supports_credentials' => false, // habilita cookies/sesiones si usas Sanctum o auth basada en cookies

];
