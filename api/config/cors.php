<?php

/*
|--------------------------------------------------------------------------
| CORS
|--------------------------------------------------------------------------
|
| La API vive en su propio subdominio y la consumen el catálogo y el panel de
| cada tienda, que están en el suyo: todas las peticiones del navegador son
| cross-origin. Se permite el dominio de la plataforma y cualquiera de sus
| subdominios, que es exactamente una tienda.
|
| `supports_credentials` va en false a propósito: Sanctum acá se usa con
| tokens Bearer, no con cookies de sesión. Ponerlo en true obligaría a
| declarar los orígenes uno por uno y no haría falta para nada.
|
*/

$host = parse_url((string) env('FRONTEND_URL', 'http://lvh.me:3000'), PHP_URL_HOST) ?: 'lvh.me';

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [],

    /* El dominio pelado y un solo nivel de subdominio: un segundo nivel no es
       una tienda y el certificado wildcard tampoco lo cubre. */
    'allowed_origins_patterns' => [
        '#^https?://([a-z0-9-]+\.)?'.preg_quote($host, '#').'(:\d+)?$#i',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 60 * 60 * 24,

    'supports_credentials' => false,

];
