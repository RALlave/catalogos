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

    /*
    |--------------------------------------------------------------------------
    | Alineación del contenido del hero
    |--------------------------------------------------------------------------
    |
    | Cada hero elige cómo se alinean sus textos y botones. Viaja al catálogo
    | como `data-align` del slide.
    |
    */

    'hero_aligns' => [
        'center',
        'left',
    ],

    /*
    |--------------------------------------------------------------------------
    | Destinos del botón del hero
    |--------------------------------------------------------------------------
    |
    | A dónde lleva el botón principal de cada hero. Se elige de esta lista y
    | nunca se escribe a mano. `category` no tiene URL fija: se arma con el
    | slug de la categoría elegida en `heroes.category_id`.
    |
    */

    'hero_links' => [
        'products' => '#products',
        'featured' => '#featured',
        'home' => '/#products',
        'contact' => '/contacto',
        'category' => null,
    ],

];
