<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Services\PlatformService;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PlatformIconController extends Controller
{
    public function __construct(private readonly PlatformService $platform) {}

    /**
     * Una URL fija para cada medida del ícono de la plataforma.
     *
     * El manifest de la app instalada es un archivo estático que se arma en el
     * build del panel, así que no puede nombrar el archivo real: el nombre
     * cambia con cada subida. Apunta acá y esta ruta redirige al archivo del
     * momento. Sin ícono cargado devuelve 404 y el navegador no ofrece
     * instalar nada.
     */
    public function __invoke(int $size): RedirectResponse
    {
        $url = $this->platform->iconUrl($size);

        abort_if($url === null, 404);

        return redirect($url);
    }
}
