<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Slugs reservados
    |--------------------------------------------------------------------------
    |
    | El slug de la tienda es su subdominio (mitienda.dominio.com), así que ya
    | no compite con las rutas de la plataforma sino con los nombres de host
    | que se usan para otra cosa. Si mañana se levanta un subdominio nuevo,
    | hay que sumarlo a esta lista antes de que alguien registre esa tienda.
    |
    */

    'reserved_slugs' => [
        'admin',
        'api',
        'cdn',
        'dev',
        'ftp',
        'mail',
        'panel',
        'smtp',
        'staging',
        'static',
        'storage',
        'webmail',
        'www',
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
