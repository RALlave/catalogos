<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Slugs reservados
    |--------------------------------------------------------------------------
    |
    | Las tiendas viven en la raíz del dominio (diseprog.com/mitienda), así que
    | su slug no puede pisar una ruta de la plataforma. Si mañana se agrega una
    | sección nueva en la raíz, hay que sumarla a esta lista.
    |
    */

    'reserved_slugs' => [
        'admin',
        'api',
        'assets',
        'css',
        'dashboard',
        'favicon.ico',
        'img',
        'images',
        'js',
        'login',
        'logout',
        'panel',
        'password',
        'profile',
        'register',
        'robots.txt',
        'signup',
        'sitemap.xml',
        'static',
        'storage',
        '_nuxt',
    ],

    /*
    |--------------------------------------------------------------------------
    | Efectos del carrusel del hero
    |--------------------------------------------------------------------------
    |
    | Cómo pasa el banner de un hero al siguiente. No es una opción de tema:
    | no viaja como data-* ni cambia colores, así que vive acá y se elige en
    | la pantalla Hero (banner) del panel, no en Apariencia.
    |
    */

    'hero_effects' => [
        'slide',
        'fade',
    ],

];
