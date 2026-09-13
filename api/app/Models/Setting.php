<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'key',
    'value',
])]
class Setting extends Model
{
    /**
     * Los logos de la plataforma y la clave con la que se guarda cada uno.
     *
     * `auth` es el de las pantallas de acceso, `panel` el de la barra lateral
     * —la misma para el superadmin y para el dueño de una tienda— e `icon` el
     * cuadrado: el favicon y el ícono de la app instalada en el escritorio. El
     * valor de cada clave es el JSON que devuelve ImageOptimizer: la variante
     * más grande en `path` y el resto en `variants`.
     */
    public const LOGOS = [
        'auth' => 'platform_logo',
        'panel' => 'platform_logo_panel',
        'icon' => 'platform_logo_icon',
    ];

    /**
     * El ícono no se mide como los otros dos: son las medidas que pide el
     * manifest de la PWA, y la ruta pública las nombra por su ancho.
     *
     * @var array<int, string>
     */
    public const ICON_SIZES = [
        192 => 'icon',
        512 => 'icon_large',
    ];

    /**
     * Los archivos de la plataforma no cuelgan de ninguna tienda, así que
     * tienen su propia carpeta al lado de `media/`.
     */
    public const DIRECTORY = 'platform';
}
